FROM nginx:latest

COPY nginx.conf /etc/nginx/nginx.conf

COPY conf.d/mysite.local.conf /etc/nginx/conf.d/mysite.local.conf

WORKDIR /data

VOLUME /data

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]