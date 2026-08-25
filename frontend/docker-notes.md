Docker for the frontend is managed from the **repo root** (`docker-compose.yml`).

Ensure **XAMPP MySQL** is running on the host before starting (see `api/notes-docker.md`).

## Dev UI (default — Vite + hot reload)

`.env.docker` sets `COMPOSE_PROFILES=dev`, so this starts `frontend-dev` + `api`:

```bash
# From the monorepo root
docker compose --env-file .env.docker up --build
```

- **Dev server:** http://localhost:8080/demo/
- **API:** http://localhost:2020/api/ (via `VITE_API_BASE_URL` in `.env.docker`)

## Production UI (nginx + static build)

Use an empty `VITE_API_BASE_URL` so the app uses relative `/api/...` paths through nginx:

```bash
VITE_API_BASE_URL= COMPOSE_PROFILES=prod docker compose --env-file .env.docker up --build
```

- **UI:** http://localhost:8080/demo/
- **API (via nginx proxy):** http://localhost:8080/api/

`API_UPSTREAM` in compose points the nginx container at `http://api:80`. Leave `VITE_API_BASE_URL` empty so the app uses relative `/api/...` paths through that proxy.
