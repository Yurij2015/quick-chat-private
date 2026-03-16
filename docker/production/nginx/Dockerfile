# Nginx production image
FROM nginx:alpine

# Copy custom Nginx configuration
COPY ./docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf

# Set the working directory to the public folder
# Note: Assets are served from the shared volume mounted at /var/www/html/public
WORKDIR /var/www/html/public

# Expose port 80 and start Nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
