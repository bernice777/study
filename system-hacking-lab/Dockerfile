# The lab follows the x86-64 register names and calling convention used in
# basic_system.pdf pp.39-49, so the architecture is intentionally amd64.
FROM ghcr.io/pwndbg/pwndbg:ubuntu24.04-amd64@sha256:86f3099a91228a292c0e50aa33f2b78d33d5d7ae35934b199e05a2b50ea5bd5b

USER root

ARG PYTHON2_VERSION=2.7.18

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        binutils \
        ca-certificates \
        curl \
        file \
        gcc \
        gcc-multilib \
        gdb \
        libc6-dev-i386 \
        make \
        python3 \
        python3-pip \
    && rm -rf /var/lib/apt/lists/* \
    && useradd --create-home --shell /bin/bash exp

# Python 2 is end-of-life, but the original KSJ FSB one-liners use its byte
# string print behaviour.  Keep this isolated compatibility runtime solely for
# the local training commands; all reference solvers continue to use Python 3.
RUN cd /tmp \
    && curl -fsSLO "https://www.python.org/ftp/python/${PYTHON2_VERSION}/Python-${PYTHON2_VERSION}.tgz" \
    && echo "da3080e3b488f648a3d7a4560ddee895284c3380b11d6de75edb986526b9a814  Python-${PYTHON2_VERSION}.tgz" | sha256sum -c - \
    && tar -xzf "Python-${PYTHON2_VERSION}.tgz" \
    && cd "Python-${PYTHON2_VERSION}" \
    && ./configure --prefix=/opt/python2.7 --enable-unicode=ucs4 \
    && make -j2 \
    && make install \
    && ln -s /opt/python2.7/bin/python2.7 /usr/local/bin/python2.7 \
    && cd / \
    && rm -rf "/tmp/Python-${PYTHON2_VERSION}" "/tmp/Python-${PYTHON2_VERSION}.tgz"

# The pinned pwndbg base currently includes Python and pwntools. Keep the
# requirement explicit so a future base-image refresh cannot silently remove
# the exploit runtime requested by the course.
RUN python3 -m pip install --no-cache-dir "pwntools==4.14.1" \
    && python3 -c 'import pwn, pwnlib; print("pwntools", pwnlib.__version__)'

WORKDIR /home/exp/exam/2.calling_convention

COPY Makefile argument.c ./
COPY gdb/ ./gdb/
COPY scripts/ ./scripts/
COPY labs/ /home/exp/ksj-labs/

RUN python2.7 --version \
    && gdb --version \
    && pwndbg -q -batch -ex 'pi import pwndbg; print("pwndbg", pwndbg.__file__)' \
    && make clean all \
    && make -C /home/exp/ksj-labs clean all \
    && cp gdb/lab.gdbinit /home/exp/.gdbinit \
    && chown -R exp:exp /home/exp

ENV HOME=/home/exp
ENV PWNDBG_NO_AUTOUPDATE=1
ENV TERM=xterm-256color

USER exp

CMD ["/bin/bash"]
