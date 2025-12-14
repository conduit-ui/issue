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

    public function testValidateMilestoneData(array $data): array
    {
        return $this->validateMilestoneData($data);
    }
}

// Use a helper function to get a fresh validator instance
function inputValidator(): ValidatesInputTestClass
{
    return new ValidatesInputTestClass;
}

describe('ValidatesInput validateOwner', function () {
    it('accepts valid owner names', function () {
        inputValidator()->testValidateOwner('owner');
        inputValidator()->testValidateOwner('owner123');
        inputValidator()->testValidateOwner('my-org');
        inputValidator()->testValidateOwner('a');

        expect(true)->toBeTrue();
    });

    it('rejects empty owner', function () {
        inputValidator()->testValidateOwner('');
    })->throws(InvalidArgumentException::class, 'Owner cannot be empty');

    it('rejects whitespace-only owner', function () {
        inputValidator()->testValidateOwner('   ');
    })->throws(InvalidArgumentException::class, 'Owner cannot be empty');

    it('rejects owner exceeding 39 characters', function () {
        inputValidator()->testValidateOwner(str_repeat('a', 40));
    })->throws(InvalidArgumentException::class, 'Owner name cannot exceed 39 characters');

    it('rejects owner with invalid characters', function () {
        inputValidator()->testValidateOwner('owner@name');
    })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

    it('rejects owner starting with hyphen', function () {
        inputValidator()->testValidateOwner('-owner');
    })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

    it('rejects owner ending with hyphen', function () {
        inputValidator()->testValidateOwner('owner-');
    })->throws(InvalidArgumentException::class, 'Owner name contains invalid characters');

    it('rejects owner with consecutive hyphens', function () {
        inputValidator()->testValidateOwner('my--org');
    })->throws(InvalidArgumentException::class, 'Owner name cannot contain consecutive hyphens');
});

describe('ValidatesInput validateRepoName', function () {
    it('accepts valid repo names', function () {
        inputValidator()->testValidateRepoName('repo');
        inputValidator()->testValidateRepoName('my-repo');
        inputValidator()->testValidateRepoName('my_repo');
        inputValidator()->testValidateRepoName('my.repo');

        expect(true)->toBeTrue();
    });

    it('rejects empty repo name', function () {
        inputValidator()->testValidateRepoName('');
    })->throws(InvalidArgumentException::class, 'Repository name cannot be empty');

    it('rejects whitespace-only repo name', function () {
        inputValidator()->testValidateRepoName('   ');
    })->throws(InvalidArgumentException::class, 'Repository name cannot be empty');

    it('rejects repo name exceeding 100 characters', function () {
        inputValidator()->testValidateRepoName(str_repeat('a', 101));
    })->throws(InvalidArgumentException::class, 'Repository name cannot exceed 100 characters');

    it('rejects repo name with invalid characters', function () {
        inputValidator()->testValidateRepoName('repo@name');
    })->throws(InvalidArgumentException::class, 'Repository name contains invalid characters');
});

describe('ValidatesInput validateIssueNumber', function () {
    it('accepts positive issue numbers', function () {
        inputValidator()->testValidateIssueNumber(1);
        inputValidator()->testValidateIssueNumber(100);
        inputValidator()->testValidateIssueNumber(999999);

        expect(true)->toBeTrue();
    });

    it('rejects zero', function () {
        inputValidator()->testValidateIssueNumber(0);
    })->throws(InvalidArgumentException::class, 'Issue number must be positive');

    it('rejects negative numbers', function () {
        inputValidator()->testValidateIssueNumber(-1);
    })->throws(InvalidArgumentException::class, 'Issue number must be positive');
});

describe('ValidatesInput validateIssueData', function () {
    it('returns sanitized data with valid title', function () {
        $result = inputValidator()->testValidateIssueData(['title' => '  My Issue  ']);

        expect($result)->toBe(['title' => 'My Issue']);
    });

    it('returns sanitized data with valid body', function () {
        $result = inputValidator()->testValidateIssueData(['body' => '  Description  ']);

        expect($result)->toBe(['body' => 'Description']);
    });

    it('validates state as open', function () {
        $result = inputValidator()->testValidateIssueData(['state' => 'open']);

        expect($result)->toBe(['state' => 'open']);
    });

    it('validates state as closed', function () {
        $result = inputValidator()->testValidateIssueData(['state' => 'closed']);

        expect($result)->toBe(['state' => 'closed']);
    });

    it('rejects invalid state', function () {
        inputValidator()->testValidateIssueData(['state' => 'invalid']);
    })->throws(InvalidArgumentException::class, 'State must be one of: open, closed');

    it('rejects non-string state', function () {
        inputValidator()->testValidateIssueData(['state' => 123]);
    })->throws(InvalidArgumentException::class, 'State must be a string');

    it('validates state_reason completed', function () {
        $result = inputValidator()->testValidateIssueData(['state_reason' => 'completed']);

        expect($result)->toBe(['state_reason' => 'completed']);
    });

    it('validates state_reason not_planned', function () {
        $result = inputValidator()->testValidateIssueData(['state_reason' => 'not_planned']);

        expect($result)->toBe(['state_reason' => 'not_planned']);
    });

    it('validates state_reason reopened', function () {
        $result = inputValidator()->testValidateIssueData(['state_reason' => 'reopened']);

        expect($result)->toBe(['state_reason' => 'reopened']);
    });

    it('rejects invalid state_reason', function () {
        inputValidator()->testValidateIssueData(['state_reason' => 'invalid']);
    })->throws(InvalidArgumentException::class, 'State reason must be one of: completed, not_planned, reopened');

    it('rejects non-string state_reason', function () {
        inputValidator()->testValidateIssueData(['state_reason' => 123]);
    })->throws(InvalidArgumentException::class, 'State reason must be a string');

    it('validates labels array', function () {
        $result = inputValidator()->testValidateIssueData(['labels' => ['bug', ' feature ']]);

        expect($result)->toBe(['labels' => ['bug', 'feature']]);
    });

    it('rejects non-array labels', function () {
        inputValidator()->testValidateIssueData(['labels' => 'bug']);
    })->throws(InvalidArgumentException::class, 'Labels must be an array');

    it('rejects non-string label', function () {
        inputValidator()->testValidateIssueData(['labels' => [123]]);
    })->throws(InvalidArgumentException::class, 'Each label must be a string');

    it('rejects empty label', function () {
        inputValidator()->testValidateIssueData(['labels' => ['']]);
    })->throws(InvalidArgumentException::class, 'Label cannot be empty');

    it('validates assignees array', function () {
        $result = inputValidator()->testValidateIssueData(['assignees' => ['user1', ' user2 ']]);

        expect($result)->toBe(['assignees' => ['user1', 'user2']]);
    });

    it('rejects non-array assignees', function () {
        inputValidator()->testValidateIssueData(['assignees' => 'user1']);
    })->throws(InvalidArgumentException::class, 'Assignees must be an array');

    it('rejects non-string assignee', function () {
        inputValidator()->testValidateIssueData(['assignees' => [123]]);
    })->throws(InvalidArgumentException::class, 'Each assignee must be a string');

    it('rejects empty assignee', function () {
        inputValidator()->testValidateIssueData(['assignees' => ['']]);
    })->throws(InvalidArgumentException::class, 'Assignee cannot be empty');

    it('validates positive milestone', function () {
        $result = inputValidator()->testValidateIssueData(['milestone' => 5]);

        expect($result)->toBe(['milestone' => 5]);
    });

    it('validates null milestone clears it', function () {
        $result = inputValidator()->testValidateIssueData(['milestone' => null]);

        // null milestone clears the milestone (GitHub API behavior)
        expect($result)->toHaveKey('milestone')
            ->and($result['milestone'])->toBeNull();
    });

    it('rejects non-integer milestone', function () {
        inputValidator()->testValidateIssueData(['milestone' => 'five']);
    })->throws(InvalidArgumentException::class, 'Milestone must be an integer or null');

    it('rejects zero milestone', function () {
        inputValidator()->testValidateIssueData(['milestone' => 0]);
    })->throws(InvalidArgumentException::class, 'Milestone must be positive');

    it('rejects negative milestone', function () {
        inputValidator()->testValidateIssueData(['milestone' => -1]);
    })->throws(InvalidArgumentException::class, 'Milestone must be positive');

    it('rejects non-string title', function () {
        inputValidator()->testValidateIssueData(['title' => 123]);
    })->throws(InvalidArgumentException::class, 'title must be a string');

    it('rejects title exceeding max length', function () {
        inputValidator()->testValidateIssueData(['title' => str_repeat('a', 257)]);
    })->throws(InvalidArgumentException::class, 'title cannot exceed 256 characters');

    it('rejects body exceeding max length', function () {
        inputValidator()->testValidateIssueData(['body' => str_repeat('a', 65537)]);
    })->throws(InvalidArgumentException::class, 'body cannot exceed 65536 characters');

    it('returns empty array for empty input', function () {
        $result = inputValidator()->testValidateIssueData([]);

        expect($result)->toBe([]);
    });
});

describe('ValidatesInput validateMilestoneData', function () {
    it('validates milestone title', function () {
        $result = inputValidator()->testValidateMilestoneData(['title' => '  v1.0  ']);

        expect($result)->toBe(['title' => 'v1.0']);
    });

    it('validates milestone state open', function () {
        $result = inputValidator()->testValidateMilestoneData(['state' => 'open']);

        expect($result)->toBe(['state' => 'open']);
    });

    it('validates milestone state closed', function () {
        $result = inputValidator()->testValidateMilestoneData(['state' => 'closed']);

        expect($result)->toBe(['state' => 'closed']);
    });

    it('rejects non-string milestone state', function () {
        inputValidator()->testValidateMilestoneData(['state' => 123]);
    })->throws(InvalidArgumentException::class, 'State must be a string');

    it('rejects invalid milestone state', function () {
        inputValidator()->testValidateMilestoneData(['state' => 'pending']);
    })->throws(InvalidArgumentException::class, 'State must be one of: open, closed');

    it('validates valid due date', function () {
        $result = inputValidator()->testValidateMilestoneData(['due_on' => '2024-12-31T00:00:00Z']);

        expect($result['due_on'])->toBe('2024-12-31T00:00:00Z');
    });

    it('validates null due date', function () {
        $result = inputValidator()->testValidateMilestoneData(['due_on' => null]);

        expect($result['due_on'])->toBeNull();
    });

    it('rejects non-string due date', function () {
        inputValidator()->testValidateMilestoneData(['due_on' => 12345]);
    })->throws(InvalidArgumentException::class, 'Due date must be a string or null');

    it('rejects empty due date', function () {
        inputValidator()->testValidateMilestoneData(['due_on' => '   ']);
    })->throws(InvalidArgumentException::class, 'Due date cannot be empty');

    it('rejects invalid due date format', function () {
        inputValidator()->testValidateMilestoneData(['due_on' => 'not-a-date']);
    })->throws(InvalidArgumentException::class, 'Due date must be a valid ISO 8601 date string');

    it('returns empty array for empty input', function () {
        $result = inputValidator()->testValidateMilestoneData([]);

        expect($result)->toBe([]);
    });
});
