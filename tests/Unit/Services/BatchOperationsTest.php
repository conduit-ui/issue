<?php

declare(strict_types=1);

use ConduitUi\GitHubConnector\Connector;
use ConduitUI\Issue\Data\Issue;
use ConduitUI\Issue\Services\BatchOperations;
use ConduitUI\Issue\Support\IssueCollection;

describe('BatchOperations', function () {
    beforeEach(function () {
        $this->connector = new Connector('fake-token');
        $this->batchOps = new BatchOperations($this->connector, 'owner', 'repo');

        $this->issues = new IssueCollection([
            Issue::fromArray(fullIssueResponse(['number' => 1, 'state' => 'open'])),
            Issue::fromArray(fullIssueResponse(['number' => 2, 'state' => 'open'])),
            Issue::fromArray(fullIssueResponse(['number' => 3, 'state' => 'open'])),
        ]);
    });

    describe('Batch Processing', function () {
        it('processes all issues in collection', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                fn ($issue) => $issue->number * 2
            );

            expect($results)->toHaveCount(3)
                ->and($results->get(0)['success'])->toBeTrue()
                ->and($results->get(0)['result'])->toBe(2)
                ->and($results->get(1)['result'])->toBe(4)
                ->and($results->get(2)['result'])->toBe(6);
        });

        it('tracks issue numbers in results', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                fn ($issue) => 'processed'
            );

            expect($results->get(0)['issue'])->toBe(1)
                ->and($results->get(1)['issue'])->toBe(2)
                ->and($results->get(2)['issue'])->toBe(3);
        });

        it('handles exceptions gracefully', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                function ($issue) {
                    if ($issue->number === 2) {
                        throw new Exception('Processing failed');
                    }

                    return 'success';
                }
            );

            expect($results)->toHaveCount(3)
                ->and($results->get(0)['success'])->toBeTrue()
                ->and($results->get(1)['success'])->toBeFalse()
                ->and($results->get(1)['error'])->toBe('Processing failed')
                ->and($results->get(2)['success'])->toBeTrue();
        });

        it('continues processing after exceptions', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                function ($issue) {
                    if ($issue->number === 1) {
                        throw new Exception('First failed');
                    }

                    return 'processed';
                }
            );

            expect($results)->toHaveCount(3)
                ->and($results->get(0)['success'])->toBeFalse()
                ->and($results->get(1)['success'])->toBeTrue()
                ->and($results->get(2)['success'])->toBeTrue();
        });
    });

    describe('Progress Tracking', function () {
        it('calls progress callback with current count', function () {
            $progressCalls = [];

            $this->batchOps->batch(
                $this->issues,
                fn ($issue) => 'processed',
                function ($current, $total, $issue) use (&$progressCalls) {
                    $progressCalls[] = ['current' => $current, 'total' => $total, 'issue' => $issue->number];
                }
            );

            expect($progressCalls)->toHaveCount(3)
                ->and($progressCalls[0]['current'])->toBe(1)
                ->and($progressCalls[0]['total'])->toBe(3)
                ->and($progressCalls[0]['issue'])->toBe(1)
                ->and($progressCalls[1]['current'])->toBe(2)
                ->and($progressCalls[2]['current'])->toBe(3);
        });

        it('works without progress callback', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                fn ($issue) => 'processed'
            );

            expect($results)->toHaveCount(3);
        });
    });

    describe('Empty Collections', function () {
        it('handles empty collection', function () {
            $emptyCollection = new IssueCollection([]);

            $results = $this->batchOps->batch(
                $emptyCollection,
                fn ($issue) => 'processed'
            );

            expect($results)->toHaveCount(0);
        });

        it('does not call progress callback for empty collection', function () {
            $progressCalled = false;

            $this->batchOps->batch(
                new IssueCollection([]),
                fn ($issue) => 'processed',
                function () use (&$progressCalled) {
                    $progressCalled = true;
                }
            );

            expect($progressCalled)->toBeFalse();
        });
    });

    describe('Complex Operations', function () {
        it('handles complex operations with multiple steps', function () {
            $results = $this->batchOps->batch(
                $this->issues,
                function ($issue) {
                    $result = [
                        'number' => $issue->number,
                        'processed' => true,
                        'timestamp' => time(),
                    ];

                    return $result;
                }
            );

            expect($results)->toHaveCount(3)
                ->and($results->get(0)['result']['processed'])->toBeTrue()
                ->and($results->get(0)['result']['number'])->toBe(1);
        });
    });
});
