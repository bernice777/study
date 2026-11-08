#include <stddef.h>
#include <stdio.h>
#include <stdlib.h>

struct Node {
    int value;
    struct Node *next;
};

int main(void)
{
    struct Node *node = malloc(sizeof(*node));
    if (node == NULL) {
        return 1;
    }
    node->value = 10;
    node->next = NULL;
    printf("sizeof(Node)=%zu next_offset=%zu node=%p\n",
           sizeof(struct Node), offsetof(struct Node, next), (void *)node);
    free(node);
    return 0;
}
