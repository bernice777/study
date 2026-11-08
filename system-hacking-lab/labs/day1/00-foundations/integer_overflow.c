#include <stdint.h>
#include <stdio.h>

int main(void)
{
    uint32_t used = 0xfffffff0U;
    uint32_t extra = 0x30U;
    uint32_t total = used + extra;

    printf("used=%#x extra=%#x total=%#x\n", used, extra, total);
    if (total < 128) {
        puts("ACCEPT");
    } else {
        puts("REJECT");
    }
    return 0;
}
