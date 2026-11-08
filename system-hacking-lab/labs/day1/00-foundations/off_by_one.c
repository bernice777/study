#include <stdio.h>

struct Data {
    char buf[4];
    char guard;
};

int main(void)
{
    struct Data data = {{0}, 'Z'};

    for (int i = 0; i <= 4; ++i) {
        data.buf[i] = 'A';
    }
    printf("guard=%c\n", data.guard);
    return 0;
}
