<?php
session_start();
session_destroy();
header("Location: ../login.php?logout=1");
exit();
?>
```

4. Press **Ctrl + S** to save!

Then open your browser and go to:
```
http://localhost/cainta_scholarship/login.php