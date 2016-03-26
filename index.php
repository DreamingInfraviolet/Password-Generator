<!DOCTYPE html>
<html>
<head>
    <title>Ps Amareth Space</title>
    <meta charset="utf-8">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="main.css" type="text/css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <center><h1>Password Amareth Space</h1></center>
        <div class="error"></div>
        <div class="row">
            <form method="POST" class="col-sm-6">
                <h2>Generate new password</h2>
                <div class="form-group">
                    <p>The name of the website. The password generator uses this to generate a unique password.
                        It is advisable that you also include your username, such as me@google.com or me@facebook.com</p>
                    <p>If the account spans multiple domains (like a google account) you should probably use the name
                        of the company as the identifier (like me@google.com or me@google). The important this is to stay
                        consistent! You should write down the identifiers you used, which should not pose a security risk.
                    <input class="form-control" type="text" name="siteid" placeholder="Website Id" required><br>
                    <input class="form-control" type="text" name="siteic" placeholder="Confirm Website Id" required><br>
                </div>
                <div class="form-group">
                    <p>Two unique passwords for additional security. They identify you as a user. Minimum 6 letters.</p>
                    <input class="form-control" type="password" name="password1" placeholder="Password 1" required><br>
                    <input class="form-control" type="password" name="password2" placeholder="Password 2" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="password1c" placeholder="Confirm Password 1" required><br>
                    <input class="form-control" type="password" name="password2c" placeholder="Confirm Password 2" required><br>
                </div>
                <div class="form-group">
                    <p>The following constrants are optional and may be used if the generated password does
                        not satisfy your website's password requirements. They are stored to the database for
                        future retrieval.</p>
                    <input class="form-control" type="number" name="minlen" placeholder="Min Length"><br>
                    <input class="form-control" type="number" name="maxlen" placeholder="Max Length"><br>
                    <input class="form-control" type="text" name="forbiddenc" placeholder="Forbidden characters (e.g. 'ab _c')"><br>
                    <input type="checkbox" name="avoiddict"> Avoid Dictionary Attacks (less readable)<br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="submit" name="submit1" value="Create"><br>
                </div>
            </form>

            <form method="POST" class="col-sm-6">
                <h2>Generate existing password</h2>
                <div class="form-group">
                    <input class="form-control" type="text" name="siteid" placeholder="Website Id" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="password1" placeholder="Password 1" required><br>
                    <input class="form-control" type="password" name="password2" placeholder="Password 2" required><br>
                </div>
                <div class="form-group">
                    <input class="form-control" type="submit" name="submit1" value="Generate"><br>
                </div>
            </form>
        </div>
    </div>
    <script   src="https://code.jquery.com/jquery-2.2.2.min.js"   integrity="sha256-36cp2Co+/62rEAAYHLmRCPIych47CvdM+uTBJwSzWjI=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
