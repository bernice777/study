set disassembly-flavor intel
set pagination off
set confirm off

break *func1
run

echo \n[func1 진입] 아직 프롤로그를 실행하기 전입니다.\n
info registers rip rsp rbp rdi rsi rdx rcx r8 r9
x/4gx $rsp
disassemble /r func1
