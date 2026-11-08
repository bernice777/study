#include <stdio.h>

struct Data {
    int secret;
    int buf[4];
};

static struct Data data = {7331, {1, 2, 3, 4}};

int main(void)
{
    int index;
    printf("index: ");
    if (scanf("%d", &index) != 1) {
        return 1;
    }
    printf("value=%d\n", data.buf[index]);
    return 0;
}
