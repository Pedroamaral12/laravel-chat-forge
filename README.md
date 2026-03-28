# laravel-chat-forge

> A comparative study of real-time chat implementations in Laravel — three approaches, one codebase to rule them all.

---

## Overview

This monorepo contains three standalone Laravel chat applications, each built on a different concurrency and real-time strategy. The goal is to compare developer experience, performance characteristics, and architectural trade-offs across the approaches.

| Project | Strategy | Use case |
|---|---|---|
| `chat-standard` | Laravel's built-in broadcasting (Reverb + Queues) | Baseline, out-of-the-box |
| `chat-custom` | Custom long-polling + event bus | Fine-grained control, no WebSocket server dependency |
| `chat-swoole` | Laravel Octane + Swoole | High-throughput, persistent memory, coroutine I/O |

---

## Repository Structure

```
laravel-chat-forge/
├── README.md                  ← you are here
├── chat-standard/             ← Project 1: Standard Laravel broadcasting
├── chat-custom/               ← Project 2: Custom implementation
└── chat-swoole/               ← Project 3: Octane + Swoole
```

Each sub-directory is a fully self-contained Laravel application with its own `.env.example`, `docker-compose.yml`, and setup instructions.

---

## Projects

### `chat-standard` — Standard Laravel Broadcasting

Uses Laravel's first-party real-time stack as documented in the official Laravel docs:

- **Laravel Reverb** as the WebSocket server
- **Laravel Echo** on the frontend
- **Redis** as the queue driver and broadcast connection
- **Laravel Queues** for dispatching broadcast events asynchronously
- Standard `ShouldBroadcast` events on the backend

This is the reference implementation. It follows the happy path and is the closest to what a greenfield Laravel project would look like today.

---

### `chat-custom` — Custom Implementation

Replaces the standard broadcasting layer with hand-rolled mechanisms:

- **Long-polling** endpoint for message delivery (no WebSocket server required)
- **Custom event bus** using Redis pub/sub directly via `Predis`
- **Optimistic UI** on the frontend to mask polling latency
- Fine-grained control over message batching, deduplication, and delivery guarantees
- A pluggable transport layer that can swap long-polling for SSE (Server-Sent Events) with a single config change

This approach trades simplicity for control — useful when you need custom delivery semantics, are behind a proxy that strips WebSocket upgrades, or want to avoid running an extra daemon.

---

### `chat-swoole` — Laravel Octane + Swoole

Runs the entire application inside a Swoole HTTP server via **Laravel Octane**:

- **Swoole coroutines** handle concurrent connections without thread overhead
- **Octane's shared memory** keeps application state warm between requests (no bootstrap cost per request)
- **Swoole WebSocket server** for native, persistent connections without Reverb
- **Coroutine-safe Redis** client via `Swoole\Coroutine\Redis`
- Benchmarks included in `/chat-swoole/benchmarks/` comparing throughput vs. the standard stack

This implementation explores the ceiling of what PHP can do for real-time workloads when the runtime constraints are lifted.

---

## Getting Started

Each project has its own setup instructions in its `README.md`. Common prerequisites across all three:

- PHP 8.3+
- Composer 2.x
- Docker & Docker Compose (recommended)
- Node.js 20+ (for frontend assets)
- Redis 7+

Clone the repo and enter any project:

```bash
git clone https://github.com/your-org/laravel-chat-forge.git
cd laravel-chat-forge/chat-standard   # or chat-custom / chat-swoole
cp .env.example .env
composer install && npm install
docker compose up -d
php artisan migrate
npm run dev
```

---

## Comparison Criteria

The three projects are designed to be evaluated across the same axes:

- **Latency** — time from message send to delivery on the recipient's screen
- **Throughput** — concurrent connections before degradation
- **Resource usage** — memory and CPU under load
- **Developer experience** — lines of code, required infrastructure, debugging complexity
- **Operational overhead** — daemons to manage, failure modes, restart strategies

A shared `benchmarks/` script at the root level runs `k6` load tests against all three and outputs a unified report.

---

## Philosophy

This is not a "best practice" repo — it is a *tradeoffs* repo. Each project is implemented to the best of its approach, not artificially handicapped. The point is to understand what you're choosing when you pick a stack, not to declare a winner.

---

## License

MIT
