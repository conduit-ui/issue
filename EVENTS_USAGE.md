# Issue Events and Timeline API

This package now includes comprehensive support for GitHub Issue Events and Timeline functionality.

## Overview

Issue events track actions performed on issues (labeled, assigned, closed, etc.), while the timeline API provides a more detailed view including comments, commits, and cross-references.

## Installation

The events functionality is automatically available when using the `IssuesService`:

```php
use ConduitUI\Issue\Services\IssuesService;
use ConduitUi\GitHubConnector\Connector;

$connector = new Connector('your-github-token');
$service = new IssuesService($connector);
```

## API Methods

### List Issue Events

Get all events for a specific issue:

```php
$events = $service->listIssueEvents('owner', 'repo', 123);

foreach ($events as $event) {
    echo "{$event->event} by {$event->actor->login} at {$event->createdAt->format('Y-m-d')}\n";
}
```

With filters:

```php
$events = $service->listIssueEvents('owner', 'repo', 123, [
    'per_page' => 50,
    'page' => 1,
]);
```

### List Issue Timeline

Get detailed timeline events including comments, commits, and cross-references:

```php
$timeline = $service->listIssueTimeline('owner', 'repo', 123);

foreach ($timeline as $event) {
    if ($event->isComment()) {
        echo "Comment: {$event->body}\n";
    } elseif ($event->isCommit()) {
        echo "Commit: {$event->commitId}\n";
    }
}
```

### List Repository Events

Get all issue events for a repository:

```php
$events = $service->listRepositoryEvents('owner', 'repo');

foreach ($events as $event) {
    echo "Event: {$event->event}\n";
}
```

With pagination:

```php
$events = $service->listRepositoryEvents('owner', 'repo', [
    'per_page' => 100,
    'page' => 1,
]);
```

## Data Transfer Objects

### IssueEvent

Represents a single issue event:

```php
class IssueEvent
{
    public int $id;
    public string $event;              // 'labeled', 'assigned', 'closed', etc.
    public ?User $actor;               // User who performed the action
    public DateTime $createdAt;
    public ?string $commitId;          // For commit-related events
    public ?string $commitUrl;
    public ?Label $label;              // For label events
    public ?User $assignee;            // For assignment events
    public ?string $milestone;         // For milestone events
}
```

Helper methods:

```php
$event->isLabelEvent();        // labeled, unlabeled
$event->isAssigneeEvent();     // assigned, unassigned
$event->isStateEvent();        // closed, reopened
$event->isMilestoneEvent();    // milestoned, demilestoned
```

### TimelineEvent

Represents a detailed timeline event:

```php
class TimelineEvent
{
    public int $id;
    public string $event;
    public ?User $actor;
    public DateTime $createdAt;
    public ?string $body;              // Comment body
    public ?string $commitId;
    public ?string $commitUrl;
    public ?Label $label;
    public ?User $assignee;
    public ?string $milestone;
    public ?string $rename;            // For renamed events
    public ?array $source;             // Cross-reference source
    public ?string $state;             // Issue state
    public ?string $stateReason;       // Reason for state change
}
```

Helper methods:

```php
$event->isComment();           // commented
$event->isCrossReference();    // cross-referenced
$event->isCommit();            // committed
$event->isReview();            // reviewed
```

## Event Types

### Common Issue Events

- `labeled` / `unlabeled` - Label added/removed
- `assigned` / `unassigned` - Assignee added/removed
- `closed` / `reopened` - Issue state changed
- `milestoned` / `demilestoned` - Milestone added/removed
- `referenced` - Referenced in commit or issue
- `renamed` - Issue title changed
- `locked` / `unlocked` - Issue locked/unlocked
- `review_requested` / `review_dismissed` - PR review actions

### Timeline Event Types

All issue events plus:

- `commented` - Comment added
- `committed` - Commit referenced
- `cross-referenced` - Referenced from another issue/PR
- `reviewed` - Pull request reviewed
- `merged` - Pull request merged
- `head_ref_deleted` - PR branch deleted

## Error Handling

All methods use the existing error handling:

```php
use ConduitUI\Issue\Exceptions\IssueNotFoundException;
use ConduitUI\Issue\Exceptions\RepositoryNotFoundException;
use ConduitUI\Issue\Exceptions\RateLimitException;

try {
    $events = $service->listIssueEvents('owner', 'repo', 123);
} catch (IssueNotFoundException $e) {
    // Issue doesn't exist
} catch (RepositoryNotFoundException $e) {
    // Repository doesn't exist
} catch (RateLimitException $e) {
    // Rate limit exceeded
    $resetTime = $e->getResetTime();
}
```

## Examples

### Filter events by type

```php
$events = $service->listIssueEvents('owner', 'repo', 123);

$labelEvents = $events->filter(fn($e) => $e->isLabelEvent());
$stateEvents = $events->filter(fn($e) => $e->isStateEvent());
```

### Get all comments from timeline

```php
$timeline = $service->listIssueTimeline('owner', 'repo', 123);

$comments = $timeline
    ->filter(fn($e) => $e->isComment())
    ->map(fn($e) => [
        'author' => $e->actor->login,
        'body' => $e->body,
        'created_at' => $e->createdAt,
    ]);
```

### Track issue state changes

```php
$events = $service->listIssueEvents('owner', 'repo', 123);

$stateChanges = $events
    ->filter(fn($e) => $e->isStateEvent())
    ->map(fn($e) => [
        'event' => $e->event,
        'actor' => $e->actor->login,
        'when' => $e->createdAt,
    ]);
```

### Get recent repository activity

```php
$recentEvents = $service->listRepositoryEvents('owner', 'repo', [
    'per_page' => 20,
]);

foreach ($recentEvents as $event) {
    echo "[{$event->createdAt->format('Y-m-d H:i')}] ";
    echo "{$event->event} by {$event->actor->login}\n";
}
```

## Testing

Comprehensive tests are available in:

- `tests/Unit/Data/IssueEventTest.php` - DTO tests
- `tests/Unit/Data/TimelineEventTest.php` - DTO tests
- `tests/Unit/Requests/EventRequestsTest.php` - Request tests
- `tests/Unit/Traits/ManagesIssueEventsTest.php` - Service tests

Run tests:

```bash
./vendor/bin/pest tests/Unit/Data/IssueEventTest.php
./vendor/bin/pest tests/Unit/Data/TimelineEventTest.php
./vendor/bin/pest tests/Unit/Requests/EventRequestsTest.php
./vendor/bin/pest tests/Unit/Traits/ManagesIssueEventsTest.php
```

## Architecture

The implementation follows the existing package patterns:

- **DTOs**: `IssueEvent` and `TimelineEvent` in `src/Data/`
- **Requests**: Three Saloon request classes in `src/Requests/Events/`
- **Service Trait**: `ManagesIssueEvents` in `src/Traits/`
- **Interface**: `ManagesIssueEventsInterface` in `src/Contracts/`
- **Integration**: Trait added to `IssuesService` and interface

All methods use:
- Existing error handling via `HandlesApiErrors` trait
- Input validation via `ValidatesInput` trait
- Type-safe DTOs with `fromArray()` and `toArray()` methods
- Illuminate Collections for return values
