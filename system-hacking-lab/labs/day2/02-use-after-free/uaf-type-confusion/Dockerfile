FROM debian:bookworm-slim AS builder
RUN apt-get update && apt-get install -y --no-install-recommends gcc libc6-dev && rm -rf /var/lib/apt/lists/*
WORKDIR /build
COPY src/challenge.c .
RUN gcc -O0 -g -Wall -Wextra -Werror -std=c11 -fno-pie challenge.c -o challenge -no-pie

FROM debian:bookworm-slim
RUN apt-get update && apt-get install -y --no-install-recommends socat && rm -rf /var/lib/apt/lists/* && useradd -m ctf
WORKDIR /home/ctf
COPY --from=builder /build/challenge ./challenge
COPY flag ./flag
RUN chown root:ctf challenge flag && chmod 0555 challenge && chmod 0440 flag
USER ctf
EXPOSE 31337
CMD ["socat", "TCP-LISTEN:31337,reuseaddr,fork", "EXEC:/home/ctf/challenge,pty,stderr,setsid,sigint,sane"]
