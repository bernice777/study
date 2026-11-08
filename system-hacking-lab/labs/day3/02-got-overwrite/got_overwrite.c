#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>

static char command[128];

int main(void)
{
    unsigned long where;
    unsigned long what;

    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);

    puts("command:");
    if (fgets(command, sizeof(command), stdin) == NULL) {
        return 1;
    }

    /* Keep a real system@plt entry while leaving the normal path harmless. */
    if (command[0] == '\0') {
        (void)system(command);
    }

    printf("where (hex): ");
    if (scanf("%lx", &where) != 1) {
        return 1;
    }
    printf("what  (hex): ");
    if (scanf("%lx", &what) != 1) {
        return 1;
    }

    *(uint64_t *)(uintptr_t)where = (uint64_t)what;
    puts(command);
    return 0;
}
