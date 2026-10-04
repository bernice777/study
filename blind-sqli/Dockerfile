FROM python:3.10-slim-bookworm

ENV PYTHONUNBUFFERED=1 \
    MYSQL_USER=dbuser \
    MYSQL_PASSWORD=dbpass \
    MYSQL_DB=user_db \
    TZ=Asia/Seoul

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential default-libmysqlclient-dev pkg-config \
    mariadb-server mariadb-client \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY deploy/ /app/
RUN pip install --no-cache-dir -r requirements.txt
COPY run-local.sh /app/run-local.sh
RUN sed -i 's/\r$//' /app/run-local.sh && chmod +x /app/run-local.sh

EXPOSE 5000
CMD ["/bin/bash", "/app/run-local.sh"]
