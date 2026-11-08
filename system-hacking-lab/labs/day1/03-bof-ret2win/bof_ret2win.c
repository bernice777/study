#include <stdio.h>
#include <unistd.h>

__attribute__((noinline, used))
void win(void)
{
    puts("KSJ{RET2WIN_VERIFIED}");
    _exit(0);
}

__attribute__((noinline))
static void vuln(void)
{
    char buf[512];

    puts("Send payload:");
    (void)read(STDIN_FILENO, buf, 640);
}

int main(void)
{
    setvbuf(stdout, NULL, _IONBF, 0);
    vuln();
    return 0;
}
