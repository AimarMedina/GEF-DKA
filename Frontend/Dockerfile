# ---------- Build ----------
FROM node:20 AS build

WORKDIR /app

COPY ./package*.json ./
RUN npm install

COPY ./ .
RUN npm run build


# ---------- Apache ----------
FROM httpd:2.4

# Limpiar config por defecto
RUN rm -rf /usr/local/apache2/htdocs/*

# Copiar build de Vite
COPY --from=build /app/dist /usr/local/apache2/htdocs

EXPOSE 80

CMD ["httpd-foreground"]

