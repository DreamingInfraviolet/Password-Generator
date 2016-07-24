<!DOCTYPE html>
<html>
<head>
    <title>Ps Quasar Waves</title>
    <meta charset="utf-8">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="main.css" type="text/css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <center><h1>Password Quasar Waves</h1></center>
        <div id="error">
        <?php
        $is1 = isset($_POST['submit1']);
        $is2 = isset($_POST['submit2']);

        if(!empty($_POST['password']))
            die("No hacking allowed");

        if($is1 and $is2)
            die("Error: both forms submitted at once.");
        $postReady = false;

        if($is1 or $is2)
        {
            if($is1)
                require_once("createlogic.php");
            if($is2)
                require_once("retrievelogic.php");
            normaliseInput();
            $postReady = verifyInput();
        }

        $answer=null;
        require_once "sqlio.php";

        try
        {
        if($postReady)
            $answer = generate(new SqlIO());
        }
        catch(Exception $e)
        {
            print $e->getMessage();
        }
        ?>
        </div>
        <?php if($answer!=null) { ?>
        <input type="text" class="form-control" readonly id="success" value="<?= $answer; ?>">
        <?php } ?>
        <div class="row">
            <form method="POST" class="col-sm-6">
                <h2>Generate existing password</h2>
                <div class="form-group">
                    <input class="form-control" type="text" value="<?= (isset($_POST['submit2']) and isset($_POST["siteid"])) ? $_POST["siteid"]:"" ?>" name="siteid" placeholder="Website Id" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="password" placeholder="Password" style="display:none"><br>
                    <input class="form-control" type="password" name="password1" placeholder="Password 1" required><br>
                    <input class="form-control" type="password" name="password2" placeholder="Password 2" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="submit" name="submit2" value="Generate"><br>
                </div>
            </form>

            <form method="POST" class="col-sm-6">
                <h2>Generate new password</h2>
                <div class="form-group">
                    <p>The name of the website. The password generator uses this to generate a unique password.
                        It is advisable that you also include your username, such as me@google.com or me@facebook.com</p>
                    <p>If the account spans multiple domains (like a google account) you should probably use the name
                        of the company as the identifier (like me@google.com or me@google). The important this is to stay
                        consistent! You should write down the identifiers you used as it will generate a different password for a different
                        identifier. Existing entries will be overwritten.</p>
                    <input class="form-control" type="text" value="<?= (isset($_POST['submit1']) and isset($_POST["siteid"])) ? $_POST["siteid"]:"" ?>" name="siteid" placeholder="Website Id" required><br>
                    <input class="form-control" type="text" value="<?= (isset($_POST['submit1']) and isset($_POST["siteidc"])) ? $_POST["siteidc"]:"" ?>" name="siteidc" placeholder="Confirm Website Id" required><br>
                </div>
                <div class="form-group">
                    <p>Two unique passwords for additional security. They identify you as a user. Minimum 6 letters.</p>
                    <input class="form-control" type="password" value="<?= (isset($_POST['submit1']) and isset($_POST["password"])) ? $_POST["password"]:"" ?>" name="password" placeholder="Password" style="display:none"><br>
                    <input class="form-control" type="password" value="<?= (isset($_POST['submit1']) and isset($_POST["password1"])) ? $_POST["password1"]:"" ?>" name="password1" placeholder="Password 1" required><br>
                    <input class="form-control" type="password" value="<?= (isset($_POST['submit1']) and isset($_POST["password2"])) ? $_POST["password2"]:"" ?>" name="password2" placeholder="Password 2" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" value="<?= (isset($_POST['submit1']) and isset($_POST["password1c"])) ? $_POST["password1c"]:"" ?>" name="password1c" placeholder="Confirm Password 1" required><br>
                    <input class="form-control" type="password" value="<?= (isset($_POST['submit1']) and isset($_POST["password2c"])) ? $_POST["password2c"]:"" ?>" name="password2c" placeholder="Confirm Password 2" required><br>
                </div>
                <div class="form-group">
                    <p>The following constrants are optional and may be used if the generated password does
                        not satisfy your website's password requirements. They are stored to the database for
                        future retrieval.</p>
                    <input class="form-control" type="number" value="<?= (isset($_POST['submit1']) and isset($_POST["minlen"])) ? $_POST["minlen"]:"" ?>" name="minlen" placeholder="Min Length"><br>
                    <input class="form-control" type="number" value="<?= (isset($_POST['submit1']) and isset($_POST["maxlen"])) ? $_POST["maxlen"]:"" ?>" name="maxlen" placeholder="Max Length"><br>
                    <input type="checkbox" name="avoiddict"> Avoid Dictionary Attacks (less readable but required by some services)<br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="submit" name="submit1" value="Create"><br>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.2.min.js" integrity="sha256-36cp2Co+/62rEAAYHLmRCPIych47CvdM+uTBJwSzWjI=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
