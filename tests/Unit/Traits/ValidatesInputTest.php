<?php

declare(strict_types=1);

use ConduitUI\Issue\Traits\ValidatesInput;

// Create a test class that uses the trait
class ValidatesInputTestClass
{
    use ValidatesInput;

    public function testValidateRepository(string $owner, string $repo): void
    {
        $this->validateRepository($owner, $repo);
    }

    public function testValidateOwner(string $owner): void
    {
        $this->validateOwner($owner);
    }

    public function testValidateRepoName(string $repo): void
    {
        $this->validateRepoName($repo);
    }

    public function testValidateIssueNumber(int $issueNumber): void
    {
        $this->validateIssueNumber($issueNumber);
    }

    public function testValidateIssueData(array $data): array
    {
        return $this->validateIssueData($data);
    }
}

describe('ValidatesInput', function () {
    beforeEach(function () {
        $this->validator = new ValidatesInputTestClass;
    });

    describe('validateOwner', function () {
        it('accepts valid owner names', function () {
            $this->validator->testValidateOwner('owner');
            $this->validator->testValidateOwner('owner123');
            $this->validator->testValidateOwner('my-org');
            $this->validator->testValidateOwner('a');

            expect(true)->toBeTrue();
        });

        it('rejects empty owner', function () {
            $this->validator->testValidateOwner('');
        })->throws(InvalidArgumentException::class, 'Owner cannot be empty');

        it('rejects whitespace-only owner', function () {
            $this->validator->testValidateOwner('   ');
        })->throws(InvalidArgumentException::class, 'Owner cannot be empty');

        it('rejects owner exceeding 39 characters', function () {
            $this->validator->testValidateOwner(str_repeat('a', 40));
        })->throws(InvalidArgumentException::class, 'Owner name cannot exceed 39 characters');

        it('rejects owner with invalid characters', function () {
            $this->validator->testValidateOwner('owner@name');
        })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

        it('rejects owner starting with hyphen', function () {
            $this->validator->testValidateOwner('-owner');
        })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

        it('rejects owner ending with hyphen', function () {
            $this->validator->testValidateOwner('owner-');
        })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

        it('rejects owner with consecutive hyphens', function () {
            $this->validator->testValidateOwner('my--org');
        })->throws(InvalidArgumentException::class, 'Owner name cannot contain consecutive hyphens');
    });

    describe('validateRepoName', function () {
        it('accepts valid repo names', function () {
            $this->validator->testValidateRepoName('repo');
            $this->validator->testValidateRepoName('my-repo');
            $this->validator->testValidateRepoName('my_repo');
            $this->validator->testValidateRepoName('my.repo');

            expect(true)->toBeTrue();
        });

        it('rejects empty repo name', function () {
            $this->validator->testValidateRepoName('');
        })->throws(InvalidArgumentException::class, 'Repository name cannot be empty');

        it('rejects whitespace-only repo name', function () {
            $this->validator->testValidateRepoName('   ');
        })->throws(InvalidArgumentException::class, 'Repository name cannot be empty');

        it('rejects repo name exceeding 100 characters', function () {
            $this->validator->testValidateRepoName(str_repeat('a', 101));
        })->throws(InvalidArgumentException::class, 'Repository name cannot exceed 100 characters');

        it('rejects repo name with invalid characters', function () {
            $this->validator->testValidateRepoName('repo@name');
        })->throws(InvalidArgumentException::class, 'Repository name contains invalid characters');
    });

    describe('validateIssueNumber', function () {
        it('accepts positive issue numbers', function () {
            $this->validator->testValidateIssueNumber(1);
            $this->validator->testValidateIssueNumber(100);
            $this->validator->testValidateIssueNumber(999999);

            expect(true)->toBeTrue();
        });

        it('rejects zero', function () {
            $this->validator->testValidateIssueNumber(0);
        })->throws(InvalidArgumentException::class, 'Issue number must be positive');

        it('rejects negative numbers', function () {
            $this->validator->testValidateIssueNumber(-1);
        })->throws(InvalidArgumentException::class, 'Issue number must be positive');
    });

    describe('validateIssueData', function () {
        it('returns sanitized data with valid title', function () {
            $result = $this->validator->testValidateIssueData(['title' => '  My Issue  ']);

            expect($result)->toBe(['title' => 'My Issue']);
        });

        it('returns sanitized data with valid body', function () {
            $result = $this->validator->testValidateIssueData(['body' => '  Description  ']);

            expect($result)->toBe(['body' => 'Description']);
        });

        it('validates state as open', function () {
            $result = $this->validator->testValidateIssueData(['state' => 'open']);

            expect($result)->toBe(['state' => 'open']);
        });

        it('validates state as closed', function () {
            $result = $this->validator->testValidateIssueData(['state' => 'closed']);

            expect($result)->toBe(['state' => 'closed']);
        });

        it('rejects invalid state', function () {
            $this->validator->testValidateIssueData(['state' => 'invalid']);
        })->throws(InvalidArgumentException::class, 'State must be one of: open, closed');

        it('rejects non-string state', function () {
            $this->validator->testValidateIssueData(['state' => 123]);
        })->throws(InvalidArgumentException::class, 'State must be a string');

        it('validates state_reason completed', function () {
            $result = $this->validator->testValidateIssueData(['state_reason' => 'completed']);

            expect($result)->toBe(['state_reason' => 'completed']);
        });

        it('validates state_reason not_planned', function () {
            $result = $this->validator->testValidateIssueData(['state_reason' => 'not_planned']);

            expect($result)->toBe(['state_reason' => 'not_planned']);
        });

        it('validates state_reason reopened', function () {
            $result = $this->validator->testValidateIssueData(['state_reason' => 'reopened']);

            expect($result)->toBe(['state_reason' => 'reopened']);
        });

        it('rejects invalid state_reason', function () {
            $this->validator->testValidateIssueData(['state_reason' => 'invalid']);
        })->throws(InvalidArgumentException::class, 'State reason must be one of: completed, not_planned, reopened');

        it('rejects non-string state_reason', function () {
            $this->validator->testValidateIssueData(['state_reason' => 123]);
        })->throws(InvalidArgumentException::class, 'State reason must be a string');

        it('validates labels array', function () {
            $result = $this->validator->testValidateIssueData(['labels' => ['bug', ' feature ']]);

            expect($result)->toBe(['labels' => ['bug', 'feature']]);
        });

        it('rejects non-array labels', function () {
            $this->validator->testValidateIssueData(['labels' => 'bug']);
        })->throws(InvalidArgumentException::class, 'Labels must be an array');

        it('rejects non-string label', function () {
            $this->validator->testValidateIssueData(['labels' => [123]]);
        })->throws(InvalidArgumentException::class, 'Each label must be a string');

        it('rejects empty label', function () {
            $this->validator->testValidateIssueData(['labels' => ['']]);
        })->throws(InvalidArgumentException::class, 'Label cannot be empty');

        it('validates assignees array', function () {
            $result = $this->validator->testValidateIssueData(['assignees' => ['user1', ' user2 ']]);

            expect($result)->toBe(['assignees' => ['user1', 'user2']]);
        });

        it('rejects non-array assignees', function () {
            $this->validator->testValidateIssueData(['assignees' => 'user1']);
        })->throws(InvalidArgumentException::class, 'Assignees must be an array');

        it('rejects non-string assignee', function () {
            $this->validator->testValidateIssueData(['assignees' => [123]]);
        })->throws(InvalidArgumentException::class, 'Each assignee must be a string');

        it('rejects empty assignee', function () {
            $this->validator->testValidateIssueData(['assignees' => ['']]);
        })->throws(InvalidArgumentException::class, 'Assignee cannot be empty');

        it('validates positive milestone', function () {
            $result = $this->validator->testValidateIssueData(['milestone' => 5]);

            expect($result)->toBe(['milestone' => 5]);
        });

        it('validates null milestone clears it', function () {
            $result = $this->validator->testValidateIssueData(['milestone' => null]);

            // null milestone clears the milestone (GitHub API behavior)
            expect($result)->toHaveKey('milestone')
                ->and($result['milestone'])->toBeNull();
        });

        it('rejects non-integer milestone', function () {
            $this->validator->testValidateIssueData(['milestone' => 'five']);
        })->throws(InvalidArgumentException::class, 'Milestone must be an integer or null');

        it('rejects zero milestone', function () {
            $this->validator->testValidateIssueData(['milestone' => 0]);
        })->throws(InvalidArgumentException::class, 'Milestone must be positive');

        it('rejects negative milestone', function () {
            $this->validator->testValidateIssueData(['milestone' => -1]);
        })->throws(InvalidArgumentException::class, 'Milestone must be positive');

        it('rejects non-string title', function () {
            $this->validator->testValidateIssueData(['title' => 123]);
        })->throws(InvalidArgumentException::class, 'title must be a string');

        it('rejects title exceeding max length', function () {
            $this->validator->testValidateIssueData(['title' => str_repeat('a', 257)]);
        })->throws(InvalidArgumentException::class, 'title cannot exceed 256 characters');

        it('rejects body exceeding max length', function () {
            $this->validator->testValidateIssueData(['body' => str_repeat('a', 65537)]);
        })->throws(InvalidArgumentException::class, 'body cannot exceed 65536 characters');

        it('returns empty array for empty input', function () {
            $result = $this->validator->testValidateIssueData([]);

            expect($result)->toBe([]);
        });
    });
});
