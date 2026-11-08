#include <stdio.h>
#include <unistd.h>

__attribute__((noinline))
static void vuln(void)
{
    char buf[64];

    printf("buf @ %p\n", (void *)buf);
    (void)read(STDIN_FILENO, buf, 160);
}

int main(void)
{
    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);
    vuln();
    return 0;
}
