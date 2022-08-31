# Blueprint Graphql Addon

## Installation

```bash
composer require --dev anwoon/blueprint-graphql-addon
```

you need to add this in your <code>filesystem disk</code>

```PHP
'graphql' => [
    'driver' => 'local',
    'root' => 'graphql',
    'throw' => false,
],
```

## Usage
Refer to [Blueprint's Basic Usage](https://github.com/laravel-shift/blueprint#basic-usage) to get started. Afterwards you can run the `blueprint:build` command to generate Graphql resources automatically. To get an idea of how easy it is you can use the example `draft.yaml` file below.

```yaml
models:
  Post:
    title: string:400
    content: longtext
    total: decimal:8,2
    status: enum:pending,successful,failed
    published_at: timestamp nullable
    author_id: id foreign:users
    relationships:
      hasMany: Comment
      belongsToMany: Site
      belongsTo: User
```

```graphql
type Post implements Model {
    id: ID!
    title: String!
    content: String!
    total: String!
    status: Status!
    published_at: Timestamp
    author_id: ID!
    comments: [Comment!] @hasMany
    sites: [Site!] @belongsToMany
    user: User! @belongsTo
}

enum Status {
    PENDING
    SUCCESSFUL
    FAILED
}
```