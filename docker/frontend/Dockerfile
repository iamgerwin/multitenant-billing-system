# docker/frontend/Dockerfile
FROM node:20-alpine

RUN apk add --no-cache git

WORKDIR /app

COPY frontend/package*.json ./

RUN npm install

COPY frontend/ .

# Generate .nuxt folder before starting dev server
RUN npm run postinstall

EXPOSE 3000

CMD ["npm", "run", "dev"]
