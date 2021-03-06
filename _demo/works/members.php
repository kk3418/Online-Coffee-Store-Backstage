<?php
session_start();

//Prevents direct connection.
if ($_SESSION['username'] == '' || $_SESSION['AorS'] != 0) {
    header('Location: ../index.php');
    //echo "<script type='text/javascript'>alert('請先登入！');</script>";
}

//Logout:
//清空SESSION
if (isset($_POST["logout"])) {
    // session_destroy();
    $_SESSION['username'] = '';
    $_SESSION['AorS'] = '';
    header('Location: ../index.php');
}

//connect to SQL
header("content-type:text/html; charset=utf-8");
$link = @mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
$result = mysqli_query($link, "set names utf8");
mysqli_select_db($link, "coffee");

//========== ADD: ADJUST COLUMN. ==========//

// get page
if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = 1;
}
//number of rows
if (isset($_POST["row_num_submit"])) {
    $_SESSION["member_row_num"] = $_POST["row_num"];
    header('Location: members.php');
} else if (isset($_SESSION["member_row_num"])) {
} else {
//初始欄數=50
    $_SESSION["member_row_num"] = 50;
}
$rowNum = $_SESSION["member_row_num"];

//總欄數:
$total_num_rows = mysqli_num_rows(mysqli_query($link, "select customerID from coffee.customers;"));
//最後一頁的頁數為:
$lastPage = floor($total_num_rows / $rowNum) + 1;
$tableOffSet = $rowNum * ($page - 1);
$showDataStartFrom = $tableOffSet + 1;
$showDataEndTo = $tableOffSet + $rowNum;
if ($showDataEndTo > $total_num_rows) {
    $showDataEndTo = $total_num_rows;
}
;
$previousPage = $page - 1;
$nextPage = $page + 1;

//===== ENDED HERE. =====//

//2 Buttons work here.
foreach ($_POST as $i => $j) {
    //Right delete button:
    if (substr($i, 0, 6) == "delete") {
        $deleteItem = ltrim($i, "delete");
        $deleteCommandText = <<<SqlQuery
        DELETE FROM coffee.customers WHERE customerID IN ('$deleteItem')
        SqlQuery;
        mysqli_query($link, $deleteCommandText);
        header('location:' . $_SERVER['REQUEST_URI'] . '');
    } //Right edit button:
    elseif (substr($i, 0, 4) == "edit") {
        //獲得customerID
        $editItem = ltrim($i, "edit");
        $editCommandText = <<<SqlQuery
        select customerID, cName, cAccount, cSex, cBirthDate, cAddress, cMobile
        from coffee.customers WHERE customerID IN ('$editItem')
        SqlQuery;
        $result = mysqli_query($link, $editCommandText);
        while ($row = mysqli_fetch_assoc($result)) {
            $_SESSION["modal_cid"] = $row["customerID"];
            $_SESSION["modal_nam"] = $row["cName"];
            $_SESSION["modal_acc"] = $row["cAccount"];
            $_SESSION["modal_sex"] = $row["cSex"];
            $_SESSION["modal_bid"] = $row["cBirthDate"];
            $_SESSION["modal_adr"] = $row["cAddress"];
            $_SESSION["modal_mob"] = $row["cMobile"];
        }
        ;
        // header('location:' . $_SERVER['REQUEST_URI'] . '');
    }
}

//ADD NEW DATA TO FORM! :
if (isset($_POST['modal_submit'])) {
    $tmp_cid = $_POST['cid'];
    $tmp_nam = $_POST['nam'];
    $tmp_acc = $_POST['acc'];
    $tmp_pwd = $_POST['pwd'];
    $tmp_sex = $_POST['sex'];
    $tmp_bid = $_POST['bid'];
    $tmp_adr = $_POST['adr'];
    $tmp_mob = $_POST['mob'];
    $insertCommandText = <<<SqlQuery
    insert into coffee.customers VALUES ('$tmp_cid','$tmp_nam','$tmp_acc','$tmp_pwd','$tmp_sex','$tmp_bid','$tmp_adr','$tmp_mob')
    SqlQuery;
    mysqli_query($link, $insertCommandText);
}

// Write table:
$front_STR1 = "<td>";
$back_STR1 = "</td>";
$front_STR2 = "<td><input type='textbox' value='";
$back_STR2 = "'></td>";
$res_fSTR = $front_STR1;
$res_bSTR = $back_STR1;

// $editbtn = "<input type='submit' value='編輯' class='btn btn-primary mb-3' name='to_edit'>";
// $updtbtn = "<input type='submit' value='送出' class='btn btn-primary mb-3' name='to_updt'>";
// $resultBtn = $editbtn;

// delete selected items:
if (isset($_POST["deleteSelected"])) {
    $selectedList = "!";

    foreach ($_POST as $i => $j) {
        if (substr($i, 0, 8) == "selected") {
            $selectedItem = ltrim($i, "selected");
            $selectedList = $selectedList . ",'" . $selectedItem . "'";
        }
    }
    $selectedList = ltrim($selectedList, "!,");
    $deleteSelectedCommandText = <<<SqlQuery
    DELETE FROM coffee.customers WHERE customerID IN ($selectedList)
    SqlQuery;
    mysqli_query($link, $deleteSelectedCommandText);
    header('location:' . $_SERVER['REQUEST_URI'] . '');
}

?>
<!DOCTYPE html>
<title>管理後台</title>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- include Bootstrap 4: cdn-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+TC|Noto+Serif+TC&display=swap" rel="stylesheet">

    <!-- I edited these stuffs.-->
    <link rel="stylesheet" type="text/css" href="../demostyle.css">
    <script src="../demoutil.js"></script>
</head>

<body>
<?php include '../parts/sidebar.php';?>
<?php include '../parts/head.php';?>
<!-- Start your code here. -->
<div class="main p-5">

<form method='post' class="card p-3">
    <div>
        <input type="submit" value="刪除勾選" name="deleteSelected" onclick="return confirm('你確定要刪除勾選資料嗎？')"
            class="btn btn-danger mb-3">
        <!--Modal toggled here.-->
        <input type="button" value="新增資料" name="edit" class="btn btn-primary ml-3 mb-3"
               data-toggle="modal" data-target="#myModal">
                <div class='float-right'>
                    <span class="mr-5">
                    <!-- show where you are -->
                    <?php echo "您在第 $page 頁，顯示資料為 $showDataStartFrom - $showDataEndTo 筆(共 $total_num_rows 筆資料)" ?>
                    </span>
                    <label for="row_num_select">請選擇顯示行數:</label>
                    <select id="row_num_select" name="row_num">
                        <option value="10" <?php if ($rowNum == 10) {echo "selected";}?>>10 </option>
                        <option value="20" <?php if ($rowNum == 20) {echo "selected";}?>>20 </option>
                        <option value="50" <?php if ($rowNum == 50) {echo "selected";}?>>50 </option>
                        <option value="100" <?php if ($rowNum == 100) {echo "selected";}?>>100</option>
                    </select>
                    <input type="submit" value="確定" name="row_num_submit" class="btn btn-primary ml-3 mb-3">
                    </span>
                </div>

    <table class="table table-striped ">
        <thead class="thead-light">
            <tr>
            <th><input type="checkbox" id="selectAll" onclick="selectAllCheckbox()"><label for="selectAll">全選</label></th>
                <th>customerID</th>
                <th>cName</th>
                <th>cAccount</th>
                <th>cSex</th>
                <th>cBirthDate</th>
                <th>cAddress</th>
                <th>cMobile</th>
                <th></th>
            </tr>
        </thead>
        <tbody>

<?php
// write table
// $commandText: $str
// 受所允許之總欄數調控
$commandText = <<<SqlQuery
select customerID, cName, cAccount, cSex, cBirthDate, cAddress, cMobile
from coffee.customers ORDER BY customerID LIMIT $rowNum OFFSET $tableOffSet
SqlQuery;

$result = mysqli_query($link, $commandText);
while ($row = mysqli_fetch_assoc($result)): ?>

            <tr>
                <td>
                    <input type="checkbox" name="<?php echo "selected" . $row["customerID"] ?>" class='checkmark'
                        style='position: relative;'>
                </td>
                <?php echo $res_fSTR . $row["customerID"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cName"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cAccount"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cSex"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cBirthDate"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cAddress"] . $res_bSTR ?>
                <?php echo $res_fSTR . $row["cMobile"] . $res_bSTR ?>
                <td>
                    <input type="submit" value="刪除" name="<?php echo "delete" . $row["customerID"] ?>"
                        class="btn btn-danger mb-3" onclick="return confirm('你確定要刪除這筆資料嗎？')">
                    <!--Modal aslo toggled at here.-->
                    <input type='button' value="編輯" name="<?php echo "edit" . $row["customerID"] ?>"
                        class="btn btn-primary mb-3">
                </td>
            </tr>
            <?php endwhile?>
        </tbody>
    </table>
</form>
<!--頁尾頁碼&按鈕顯示:-->
<div class="d-flex justify-content-center align-items-center flex-column  m-5">
        <!-- page select -->
        <div class="m-3">
            <a class='m-2 btn btn-info' href='members.php?page=1'>第一頁</a>
            <a class='m-2 btn btn-info' href='members.php?page=<?php echo ($page <= 1) ? "1" : $previousPage; ?>'>上一頁</a>
            <a class='m-2 btn btn-info' href='members.php?page=<?php echo ($page >= $lastPage) ? $lastPage : $nextPage; ?>'>下一頁</a>
            <a class='m-2 btn btn-info' href='members.php?page=<?php echo $lastPage; ?>'>最尾頁</a>
        </div>
            <div>
            <?php
for ($i = 1; $i <= 3 && $i <= $lastPage; $i++) {
    echo " <a class='m-2' href='members.php?page=$i'>$i</a>";
}
if ($page <= 6) {
    for ($i = 4; $i <= ($page + 2) && $i <= $lastPage; $i++) {
        echo " <a class='m-2' href='members.php?page=$i'>$i</a>";
    }
} else {
    echo "<span>......</span>";
    for ($i = ($page - 2); $i <= ($page + 2) && $i <= $lastPage; $i++) {
        echo " <a class='m-2' href='members.php?page=$i'>$i</a>";
    }
}
if ($lastPage - $page <= 5) {
    for ($i = ($page + 3); $i <= $lastPage; $i++) {
        echo " <a class='m-2' href='members.php?page=$i'>$i</a>";
    }
} else {
    echo "<span>......</span>";
    for ($i = ($lastPage - 2); $i <= $lastPage; $i++) {
        echo " <a class='m-2' href='members.php?page=$i'>$i</a>";
    }
}

?>
<!--頁尾頁碼&按鈕結束-->
</div>
<!-- Dummy frame. -->
<iframe name="thisframe"></iframe>
<!-- Modal -->
<div class="modal fade" id="myModal">
<div class="modal-dialog">
    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
            <h4 class="modal-title">資料變更:</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form method="post" action=''>
        <!-- Modal body -->
        <div class="modal-body">
            <tr>

                    <th>customerID:<input type="text" name='cid'>
                    </th>
                    <hr>
                    <th>cName: <input type="text" name='nam'></th>
                    <hr>
                    <th>cAccount: <input type="text" name='acc'>
                    </th>
                    <hr>
                    <th>cPassword: <input type="text" name='pwd'>
                    </th>
                    <hr>
                    <th>cSex: <input type="text" name='sex'></th>
                    <hr>
                    <th>cBirthDate:<input type="date" name='bid'>
                    </th>
                    <hr>
                    <th>cAddress: <input type="text" name='adr'>
                    </th>
                    <hr>
                    <th>cMobile: <input type="text" name='mob'></th>
            </tr>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
            <input type="submit" name="modal_submit" value='submit' class="btn btn-primary"></input>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
        </form>
    </div>
</div>
</div>
<!-- End your code here. -->
<?php include '../parts/footer.php';?>
</body>