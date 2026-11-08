#include <dlfcn.h>
#include <stdio.h>
#include <stdlib.h>

int main(void)
{
    char stack[8];
    void *heap = malloc(16);
    void *libc_puts = dlsym(RTLD_DEFAULT, "puts");
    if (heap == NULL || libc_puts == NULL) {
        return 1;
    }
    printf("stack=%p\n", (void *)stack);
    printf("heap=%p\n", heap);
    printf("puts=%p\n", libc_puts);
    free(heap);
    return 0;
}
