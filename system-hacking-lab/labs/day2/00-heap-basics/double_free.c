#include <stdlib.h>

int main(void)
{
    char *ptr = malloc(0x30);
    if (ptr == NULL) {
        return 1;
    }
    free(ptr);
    free(ptr);
    return 0;
}
