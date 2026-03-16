# Stage 1: Build assets
FROM node:22-alpine AS builder

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install dependencies and build assets
RUN npm install && npm run build

# Stage 2: Nginx production image
FROM nginx:alpine

# Copy custom Nginx configuration
COPY ./docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf

# Copy Laravel's public assets from the builder stage
COPY --from=builder /var/www/html/public /var/www/html/public

# Set the working directory to the public folder
WORKDIR /var/www/html/public

# Expose port 80 and start Nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
