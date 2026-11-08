#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

struct Record {
    char pad[16];
    char key[16];
};

int main(void)
{
    setvbuf(stdout, NULL, _IONBF, 0);
    struct Record *first = malloc(sizeof(*first));
    if (first == NULL) {
        return 1;
    }
    memset(first, 0, sizeof(*first));
    memcpy(first->key, "SECRET", 6);
    void *old_address = first;
    free(first);

    struct Record *second = malloc(sizeof(*second));
    if (second == NULL) {
        return 1;
    }
    printf("reused=%d residual=", old_address == (void *)second);
    (void)write(STDOUT_FILENO, second->key, 6);
    putchar('\n');
    free(second);
    return 0;
}
