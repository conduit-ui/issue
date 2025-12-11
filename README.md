# GitHub Issue Management Built for Scale

Stop manually triaging issues. Start automating the repetitive work that keeps your team from shipping.

Bulk label, assign, close, and route issues across repositories with clean PHP code. Built for teams managing hundreds of issues across multiple projects.

[![Latest Version](https://img.shields.io/packagist/v/conduit-ui/issue.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/issue)
[![MIT License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/conduit-ui/issue.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/issue)

## Installation

```bash
composer require conduit-ui/issue
```

## Why This Exists

Your team creates 50+ issues per week. You're manually labeling bugs, assigning features to team members, and closing duplicates. This package gives you the tools to automate all of it.

## Quick Start

```php
use ConduitUI\Issue\Issue;

// Find and update a single issue
Issue::find('owner/repo', 123)
    ->addLabels(['bug', 'priority-high'])
    ->assignTo('username')
    ->save();

// Bulk operations across multiple issues
Issue::query('owner/repo')
    ->state('open')
    ->label('needs-triage')
    ->get()
    ->each(fn($issue) => $issue
        ->removeLabels(['needs-triage'])
        ->addLabels(['triaged'])
        ->save()
    );
```

## Core Features

**Smart Labeling**
```php
// Add, remove, or replace labels in bulk
Issue::find('owner/repo', 456)
    ->addLabels(['bug', 'urgent'])
    ->removeLabels(['question'])
    ->save();
```

**Assignment & Routing**
```php
// Route issues to team members based on labels
Issue::query('owner/repo')
    ->label('frontend')
    ->open()
    ->get()
    ->each(fn($issue) => $issue->assignTo('frontend-team'));
```

**Bulk State Management**
```php
// Close stale issues automatically
Issue::query('owner/repo')
    ->state('open')
    ->updatedBefore(now()->subMonths(6))
    ->get()
    ->each(fn($issue) => $issue->close('Closing due to inactivity'));
```

**Advanced Filtering**
```php
Issue::query('owner/repo')
    ->author('username')
    ->assignee('team-member')
    ->labels(['bug', 'priority-high'])
    ->since(now()->subWeek())
    ->sort('created', 'desc')
    ->get();
```

**Comment Management**
```php
// Add automated responses
$issue = Issue::find('owner/repo', 789);
$issue->comment('Thanks for reporting! Our team will investigate.');

// Get all comments
$comments = $issue->comments();
```

**Lock & Unlock**
```php
// Lock heated discussions
$issue->lock('too heated');

// Unlock when ready
$issue->unlock();
```

## Usage Patterns

### Static API (Quick Operations)
```php
use ConduitUI\Issue\Issue;

$issue = Issue::find('owner/repo', 123);
$issues = Issue::query('owner/repo')->open()->get();
```

### Instance API (Multiple Operations)
```php
use ConduitUI\Issue\IssueManager;

$manager = new IssueManager('owner/repo');
$issue = $manager->find(123);
$issues = $manager->query()->open()->get();
```

## Data Objects

All responses return strongly-typed DTOs:

```php
$issue->id;           // int
$issue->number;       // int
$issue->title;        // string
$issue->state;        // 'open' | 'closed'
$issue->author;       // User object
$issue->assignees;    // Collection of User objects
$issue->labels;       // Collection of Label objects
$issue->createdAt;    // Carbon instance
$issue->updatedAt;    // Carbon instance
$issue->closedAt;     // ?Carbon instance
```

## Real-World Use Cases

**Automated Triage Bot**
```php
// Label bugs automatically based on title keywords
Issue::query('owner/repo')
    ->state('open')
    ->created('after', now()->subHour())
    ->get()
    ->filter(fn($issue) => str_contains(strtolower($issue->title), 'bug'))
    ->each(fn($issue) => $issue->addLabels(['bug', 'needs-triage']));
```

**Team Assignment**
```php
// Route issues to on-call team member
$oncall = getOncallEngineer(); // Your function

Issue::query('owner/repo')
    ->label('incident')
    ->open()
    ->get()
    ->each(fn($issue) => $issue->assignTo($oncall));
```

**Stale Issue Cleanup**
```php
// Close issues with no activity for 90 days
Issue::query('owner/repo')
    ->state('open')
    ->updatedBefore(now()->subDays(90))
    ->get()
    ->each(function($issue) {
        $issue->comment('Closing due to inactivity. Please reopen if still relevant.');
        $issue->close();
    });
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="issue-config"
```

Set your GitHub token in `.env`:

```env
GITHUB_TOKEN=your-github-token
```

## Requirements

- PHP 8.2+
- GitHub personal access token with `repo` scope

## Testing

```bash
composer test
```

## Related Packages

- [conduit-ui/pr](https://github.com/conduit-ui/pr) - Pull request automation
- [conduit-ui/repo](https://github.com/conduit-ui/repo) - Repository governance
- [conduit-ui/connector](https://github.com/conduit-ui/connector) - GitHub API transport layer

## Enterprise Support

Managing issues across 100+ repositories? Contact [jordan@partridge.rocks](mailto:jordan@partridge.rocks) for custom automation solutions.

## License

MIT License. See [LICENSE](LICENSE.md) for details.
