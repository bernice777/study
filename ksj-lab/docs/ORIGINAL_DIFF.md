# ORIGINAL_DIFF — 교육용 복사본(apps/ksj-sbadmin) 변경 기록

> 원본: `original/sbadmin/` (읽기전용, 무수정). 복사본: `apps/ksj-sbadmin/`.
> **취약한 SQL 문자열 결합은 그대로 유지**했다. 변경은 (1)DB 접속 설정 (2)쿼리 로깅 (3)Trace/계측 (4)SQLI 범위 제어 에 한정.

## 변경 파일 요약

| 파일 | 변경 내용 | 취약점 유지? |
|---|---|---|
| init.php | 하드코딩 `root/1234/localhost` → env 기반 `lab_connect()`(lab_app,utf8mb4) | 해당없음(접속설정) |
| index.php | `?p=` 임의 include(LFI) 화이트리스트 차단 | LFI는 범위밖→차단 |
| act/login.php | `lab_query()` 계측, fetch 가드 | ✅ 문자열 결합 유지 |
| act/register.php | `lab_query()` 계측 | ✅ INSERT 결합 유지 |
| act/write.php | `lab_query()` 계측 + 업로드 SQLI모드 차단 | ✅ 테이블/값 결합 유지 |
| act/download.php | SQLI모드 전체 차단(임의파일읽기) + 계측 | ✅ 쿼리 결합 유지 |
| pages/board.php | `lab_query()` 계측, fetch 가드 | ✅ 테이블명 결합 유지 |
| pages/read.php | `lab_query()` 계측, fetch 가드 | ✅ 숫자형 결합 유지 |

## 파일별 unified diff (original → apps/ksj-sbadmin)

### init.php
```diff
@@ -1,10 +1,9 @@
 <?php
-    ini_set("display_errors", true);
-
+    // [LAB-MOD] 원본: $db = mysqli_connect("localhost","root","1234"); mysqli_select_db($db,"sbadmin");
+    //           → 하드코딩 제거, 환경변수 기반 최소권한(lab_app) + utf8mb4 연결로 교체.
+    //           lab_connect() 는 labkit.php(auto_prepend)에서 정의됨.
     session_start();
-
-    $db = mysqli_connect("localhost", "root", "1234");
-    mysqli_select_db($db, "sbadmin");
+    $db = lab_connect();
 
     function isLogin(){
         if (isset($_SESSION['uIdx'])) return true;
```

### index.php
```diff
@@ -2,16 +2,17 @@
     include("init.php");
     if (isset($_GET['p']) && $_GET['p'] == "register.php") {
         include("./pages/register.php");
-    }else if (!isLogin()){
+    } else if (!isLogin()){
         include("./pages/login.php");
     } else {
-        if (isset($_GET['p'])){
-            $page = $_GET['p'];
-        } else {
+        $page = isset($_GET['p']) ? $_GET['p'] : "home.php";
+        // [LAB-MOD] 원본은 include("./pages/".$page) 로 임의 파일 include 가능(LFI).
+        //           SQLI 범위 밖이므로 화이트리스트로 차단(원본은 original/ 에 보존).
+        if (!in_array($page, lab_allowed_pages(), true)) {
+            lab_scope_deny('lfi_include:'.$page);
             $page = "home.php";
         }
         include("./theme/head.php");
         include("./pages/".$page);
         include("./theme/foot.php");
     }
-
```

### act/login.php
```diff
@@ -1,12 +1,15 @@
 <?php
     include("../init.php");
 
-    $email = $_POST['email'];
-    $password = $_POST['password'];
+    $email = $_POST['email'] ?? '';
+    $password = $_POST['password'] ?? '';
 
-    $result = mysqli_query($db, "select * from users where email='{$email}' and password='{$password}'");
+    // [LAB] 취약 문자열 결합 유지. lab_query 로 최종 SQL/결과만 기록.
+    $sql = "select * from users where email='{$email}' and password='{$password}'";
+    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'1','note'=>'ksj login (string-based)']);
 
-    $row = mysqli_fetch_assoc($result);
+    // [LAB-MOD] PHP8 에서 fetch(false) 는 TypeError 이므로 가드 (에러 자체는 Observer 로 관찰)
+    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;
 
     if (isset($row['idx'])){
         $_SESSION['uIdx'] = $row['idx'];
```

### act/register.php
```diff
@@ -1,9 +1,11 @@
 <?php
     include("../init.php");
 
-    $email = $_POST['email'];
-    $password = $_POST['password'];
+    $email = $_POST['email'] ?? '';
+    $password = $_POST['password'] ?? '';
 
-    $result = mysqli_query($db, "insert into users (email, password) values ('{$email}', '{$password}')");
+    // [LAB] 취약 INSERT 문자열 결합 유지.
+    $sql = "insert into users (email, password) values ('{$email}', '{$password}')";
+    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'insert','note'=>'ksj register insert SQLi']);
 
     die("<script>alert('success');window.location.href='../';</script>");
```

### act/write.php
```diff
@@ -1,19 +1,24 @@
 <?php
     include("../init.php");
 
-    $uIdx = $_SESSION['uIdx'];
-    $title = $_POST['title'];
-    $contents = $_POST['contents'];
+    $uIdx = $_SESSION['uIdx'] ?? 0;
+    $title = $_POST['title'] ?? '';
+    $contents = $_POST['contents'] ?? '';
     $upPath = "";
     $upName = "";
-    $type = $_POST['type'];
+    $type = $_POST['type'] ?? 'freeboard';
 
     if (isset($_FILES['upload']) && !empty($_FILES['upload']['name'])){
+        // [LAB-MOD] 파일 업로드는 SQLI 범위 밖(웹셸 위험) → SQLI 모드에서 차단.
+        //           원본 업로드 코드는 original/ 에 보존.
+        lab_scope_deny('upload');
         $upName = $_FILES['upload']['name'];
         $upPath = "../uploads/".$_FILES['upload']['name'];
         move_uploaded_file($_FILES['upload']['tmp_name'], $upPath);
     }
 
-    $result = mysqli_query($db, "insert into {$type} (uIdx, title, contents, upPath, upName) values ('{$uIdx}', '{$title}', '{$contents}', '{$upPath}', '{$upName}')");
+    // [LAB] 취약 문자열 결합(테이블명 $type + 값) 유지.
+    $sql = "insert into {$type} (uIdx, title, contents, upPath, upName) values ('{$uIdx}', '{$title}', '{$contents}', '{$upPath}', '{$upName}')";
+    $result = lab_query($db, $sql, __FILE__, __LINE__, ['note'=>'ksj board write (table+value inj)']);
 
     die("<script>window.location.href='../';</script>");
```

### act/download.php
```diff
@@ -1,11 +1,16 @@
 <?php
     include("../init.php");
 
-    $type = $_GET['t'];
-    $idx = $_GET['i'];
+    // [LAB-MOD] 이 엔드포인트는 select 후 file_get_contents($row['upPath']) 로
+    //           임의 파일을 읽어 반환한다(LFI/임의파일읽기). SQLI 범위 밖 → 차단.
+    lab_scope_deny('file_download');
 
-    $result = mysqli_query($db, "select * from {$type} where idx={$idx}");
-    $row = mysqli_fetch_assoc($result);
+    $type = $_GET['t'] ?? 'freeboard';
+    $idx = $_GET['i'] ?? 0;
+
+    $sql = "select * from {$type} where idx={$idx}";
+    $result = lab_query($db, $sql, __FILE__, __LINE__, ['level'=>'2','note'=>'ksj download (numeric)']);
+    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;
 
     header("Content-Type: application/octet-stream");
     header("Content-Disposition: attachment; filename={$row['upName']}");
```

### pages/board.php
```diff
@@ -1,5 +1,5 @@
 <?php
-    $type = $_GET['t'];
+    $type = $_GET['t'] ?? 'freeboard';
 ?>
                 <div class="container-fluid">
 
@@ -22,8 +22,9 @@
                                 </thead>
                                 <tbody>
                                     <?php
-                                        $result = mysqli_query($db, "select * from {$type} order by idx desc");
-                                        while($row = mysqli_fetch_assoc($result)):
+                                        // [LAB] 취약 문자열 결합(테이블명 $type) 유지.
+                                        $result = lab_query($db, "select * from {$type} order by idx desc", __FILE__, __LINE__, ['level'=>'3','note'=>'board list (table inj)']);
+                                        while($result instanceof mysqli_result && $row = mysqli_fetch_assoc($result)):
                                     ?>
                                     <tr style="cursor:pointer;" onclick="window.location.href='?p=read.php&t=<?=$type?>&i=<?=$row['idx']?>';">
                                         <td><?=$row['idx']?></td>
```

### pages/read.php
```diff
@@ -1,13 +1,15 @@
 <?php
-    $type = $_GET['t'];
-    $idx = $_GET['i'];
-    $result = mysqli_query($db, "select * from {$type} where idx={$idx}");
-    $row = mysqli_fetch_assoc($result);
+    $type = $_GET['t'] ?? 'freeboard';
+    $idx = $_GET['i'] ?? 0;
+    // [LAB] 숫자형 SQLi 유지 (idx 는 따옴표 없음). lab_query 로 최종 SQL/결과 기록.
+    $result = lab_query($db, "select * from {$type} where idx={$idx}", __FILE__, __LINE__, ['level'=>'2','note'=>'read (numeric)']);
+    $row = ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : null;
 
-    mysqli_query($db, "update {$type} set hit = hit + 1 where idx={$idx}");
-    
-    $result = mysqli_query($db, "select * from users where idx='{$row['uIdx']}'");
-    $writer = mysqli_fetch_assoc($result)['email'];
+    lab_query($db, "update {$type} set hit = hit + 1 where idx={$idx}", __FILE__, __LINE__, ['note'=>'hit++']);
+
+    // [LAB] 저장된 uIdx 를 다시 쿼리에 사용 (2차성 조회 흐름).
+    $r2 = lab_query($db, "select * from users where idx='{$row['uIdx']}'", __FILE__, __LINE__, ['note'=>'writer lookup (stored value -> query)']);
+    $writer = ($r2 instanceof mysqli_result) ? (mysqli_fetch_assoc($r2)['email'] ?? '') : '';
 ?>
                 <div class="container-fluid">
 
```

## 변경하지 않은 것
- 취약한 SQL 문자열 결합 로직 (교육 목적상 유지)
- 저장형 XSS 출력(board/read) — SQLi 실습 흐름 유지 (Observer 자체는 escape)
- 원본 파일 트리(original/) — 무수정, 읽기전용
