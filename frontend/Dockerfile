FROM node:20-alpine as builder

WORKDIR /app
COPY app/ .

RUN npm install && npm run build

# Étape pour servir l'app (Nitro standalone ou node server)
FROM node:20-alpine

WORKDIR /app
COPY --from=builder /app/.output ./.output
COPY --from=builder /app/node_modules ./node_modules

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
