set pagination off
set confirm off
set print pretty on
set environment FLAG_PATH ./flag

break debug_after_a_alloc
commands
  silent
  printf "[1] A allocated: g_session=%p, sizeof(Session)=%zu\n", g_session, sizeof(Session)
  printf "    authenticated offset=%zu, access_level offset=%zu\n", &((Session *)0)->authenticated, &((Session *)0)->access_level
  x/6gx g_session
  continue
end

break free
condition $bpnum $rdi == g_session
commands
  silent
  printf "[2] free called once with pointer=%p\n", $rdi
  continue
end

break debug_after_a_free
commands
  silent
  printf "[3] A freed, dangling g_session=%p, freed state=%d\n", g_session, g_session_freed
  x/6gx g_session
  continue
end

break debug_after_b_alloc
commands
  silent
  printf "[4] B allocated: g_memo=%p; A dangling=%p; same=%d\n", g_memo, g_session, g_memo == (Memo *)g_session
  printf "    memory before B input:\n"
  x/6gx g_memo
  continue
end

break debug_after_b_input
commands
  silent
  printf "[5] memory after B input:\n"
  x/6gx g_memo
  printf "    B.category=%#lx B.value=%#lx\n", g_memo->category, g_memo->value
  continue
end

break debug_before_check
commands
  silent
  printf "[6] dangling A reads authenticated=%#lx access_level=%#lx\n", g_session->authenticated, g_session->access_level
  continue
end

break debug_before_flag
commands
  silent
  printf "[7] condition true; print_flag will be called\n"
  continue
end

run < gdb-input.txt
