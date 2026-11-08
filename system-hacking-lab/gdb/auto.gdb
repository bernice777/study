set disassembly-flavor intel
set pagination off
set confirm off

break *main
run
disassemble /r main

echo \n[자동 확인] main 진입 직후 레지스터와 스택\n
info registers rip rsp rbp
x/6gx $rsp
