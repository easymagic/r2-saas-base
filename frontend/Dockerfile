# Build stage
FROM node:20-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Optional build-time env (baked into the static bundle)
ARG API_BASE_URL=
ARG VITE_API_BASE_URL=
ARG VITE_API_X_TOKEN=
ENV API_BASE_URL=$API_BASE_URL \
    VITE_API_BASE_URL=$VITE_API_BASE_URL \
    VITE_API_X_TOKEN=$VITE_API_X_TOKEN \
    VITE_BUILD_OUT_DIR=dist

RUN npm run build

# Production stage
FROM nginx:1.27-alpine

COPY nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY --from=build /app/dist /usr/share/nginx/html/demo

ENV API_UPSTREAM=http://api:80

EXPOSE 80
