<?php
session_start();
session_destroy();
header("Location: student_login.php?logout=1");
exit();
?>
```

Press **Ctrl + S** on all files! Then go to:
```
http://localhost/cainta_scholarship/student_login.php