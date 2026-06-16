<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
include 'constants.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit();
}


/* ==============================
   Function to get client IP
============================== */
function get_client_ip() {
    $ipaddress = '';

    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

/* ==============================
   Form Submit Logic
============================== */

if (
    (!isset($_COOKIE['formfilled'])) &&
    isset($_POST['phone']) &&
    strlen($_POST['phone']) > 6
) {

    setcookie('formfilled', 'yes');
    $mailsent = true;
    // $name         = $_POST['fname'].' '.$_POST['lname'];
   $name         = $conn->real_escape_string($_POST['name']);
    $email        = $conn->real_escape_string($_POST['email']);
    $mobile       = $conn->real_escape_string($_POST['phone']);
    $countrycode  = $conn->real_escape_string($_POST['countrycode'] ?? '');
    $countryname  = $conn->real_escape_string($_POST['countryname'] ?? '');
    $source       = $conn->real_escape_string($_POST['source'] ?? '');
    $subject      = $conn->real_escape_string($_POST['subject'] ?? '');
    $ipaddress    = get_client_ip();

    /* FLOORPLAN DOWNLOAD FILE */
    $download_file = $_POST['download_file'] ?? '';

    /* Cookies */
    $keyword            = $_COOKIE['Lead_Keyword'] ?? '';
    $placement          = $_COOKIE['Lead_Placement'] ?? '';
    $device             = $_COOKIE['Lead_Device'] ?? '';
    $campaignsource     = $_COOKIE['utm_source'] ?? '';
    $campaignsubsource  = $_COOKIE['utm_sub_source'] ?? '';
    $utmM               = $_COOKIE['utm_medium'] ?? '';
    $utmC               = $_COOKIE['utm_campaign'] ?? '';
    $adcreative         = $_COOKIE['utm_adcreative'] ?? '';
    $adset              = $_COOKIE['utm_adset'] ?? '';
    $mf_medium          = $_COOKIE['mf_medium'] ?? '';
    $mf_campaignid      = $_COOKIE['mf_campaignid'] ?? '';
    $cid                = $_COOKIE['cid'] ?? '';
    $gclid              = $_COOKIE['gclid'] ?? '';

    $websitename = 'Codename King Life';
    $pageurl     = 'Codename King Life';
    $duplicate   = "No";
    $reason      = '';

    /* Duplicate Check */
    $sqlDuplicate = "SELECT * FROM `lead`
                     WHERE mobile='".$mobile."'
                     AND email='".$email."'";

    $result = $conn->query($sqlDuplicate);
    $num    = $result->num_rows;

    if ($num == 0) {
        $duplicate = "No";
    } else {
        $duplicate = "Yes";
    }

    if ($campaignsource == NULL || $campaignsource == '') {
        $campaignsource = "Website";
    }

    /* Insert Lead */
    if (isset($mobile) && $mobile != '' && $email != '') {

        $lead_sql = $conn->prepare("
            INSERT INTO `lead`
            (
                name,
                email,
                countrycode,
                mobile,
                campaignsource,
                campaignsubsource,
                placement,
                device,
                adset,
                adcreative,
                keyword,
                capturefromname,
                websitename,
                createddate,
                utmM,
                utmC,
                duplicate,
                last_reson,
                ip_address
            )
            VALUES
            (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $lead_sql->bind_param(
            "sssssssssssssssssss",
            $name,
            $email,
            $countrycode,
            $mobile,
            $campaignsource,
            $campaignsubsource,
            $placement,
            $device,
            $adset,
            $adcreative,
            $keyword,
            $source,
            $websitename,
            date('Y-m-d H:i:s'),
            $utmM,
            $utmC,
            $duplicate,
            $reason,
            $ipaddress
        );

        $lead_qry = $lead_sql->execute();

        $last_insertid = mysqli_insert_id($conn);

        $session = $_POST['session_id'] ?? '';

        $map = $conn->prepare("
            UPDATE click_logs
            SET lead_id=?
            WHERE session_id=? AND lead_id IS NULL
        ");

        $map->bind_param("ss", $last_insertid, $session);
        $map->execute();
    }

} else {
    $mailsent = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <title>Codename King Life </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="owl/owl.carousel.min.css">
    <link rel="stylesheet" href="owl/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
        integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <link rel="stylesheet" href="css/style.css?v=2.20">
    <link rel="stylesheet" href="css/home.css?v=2.24">
     <link rel="icon" type="image/x-icon" href="image/logo/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        header {
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        .banner {
            background-image: url(img/white.webp);
            background-repeat: no-repeat;
            background-size: cover;
            position: fixed;
            z-index: 9;
            width: 100%;
            height: 100vh;
            text-align: center;
        }
        .head1 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
        }

        .poverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
         background-color: rgba(179, 145, 115, 0.85);
    
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
            overflow-x: hidden;
            z-index: 99999;
            overflow-y: auto;
        }

        .head1 {
            background: #ffffff !important;


        }

        .nav-link-item {
            color: #1a1a2e !important;
        }

        .headspan2 img {
            filter: brightness(0) !important;
        }
        .content {
            padding-top: 20px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .icon {

            font-size: 9rem;
            color: #B39173;
        }

        h1 {
            font-size: 55px;
            color: #B39173;
            text-transform: uppercase;
            font-weight: 900;
        }

        p {
            font-size: 20px;
            color: #000;
            margin-top: 20px;
        }

        .backhome a {
            padding: 10px 15px;
            text-decoration: none;
            display: inline-block;
            background: #B39173;
            margin-top: 30px;
            font-size: 25px;
            border-radius: 10px;
            color: #fff;
        }

        <blade media|%20(max-width%3A%20768px)%20%7B>#header .navbar-container {
            display: block;
            position: relative;
            height: 83px;
        }
        }

        <blade media|%20only%20screen%20and%20(max-width%3A%20991px)%20%7B>.content {
            width: 70%;
        }
        }
    </style>
</head>

<body>

    <!--============================  Nav Start ===============================-->
    <div class="container-fluid head1">
        <div class="head1-inner">

            <!--============== LEFT: Logo ================-->
            <div class="nav-logo">
                <a>
                    <img class="img-fluid headlogo" src="image/logo/codename_king.png" alt="logo">
                </a>
            </div>

            <!-- ================= CENTER: Menu links — sirf desktop  =============-->
            <nav class="nav-center-links d-none d-lg-flex">

                <a href="#exclusivity" class="nav-link-item" data-log="nav-office">Exclusivity</a>
                <a href="#overview" class="nav-link-item" data-log="nav-overview">OVERVIEW</a>
                <a href="#location" class="nav-link-item" data-log="nav-location">LOCATION</a>
                <a href="#contact-Us" class="nav-link-item" data-log="nav-contact">CONTACT US</a>
            </nav>

            <!--================= RIGHT: Button (desktop) + Hamburger (mobile)=============== -->
            <div class="nav-right">
                <a href="javascript:void(0)" class="enquire-nav-btn d-none d-lg-inline-block" data-bs-toggle="modal"
                    data-bs-target="#contactModal" data-log="header-enquire-btn">
                    Enquire Now
                </a>
                <span class="headspan2 d-lg-none">
                    <a onclick="openNav()" data-log="header-menu-open">
                        <img class="img-fluid" src="image/logo/icon2.png" alt="menu">
                    </a>
                </span>
            </div>

        </div>
    </div>
    <!--============================  Nav End Desktop  ===============================-->

    <!--============================ MOBILE MENU Start ===============================-->
    <div id="myNav" class="poverlay">
        <div class="container-fluid menucf">
            <div class="container headcon">
                <div class="row">
                    <div class="col-6 mobmainmenu1">
                        <!-- <a data-log="navbar-logo-click">
                            <img class="img-fluid" src="image/codename_king.png" alt="Codename King Life Logo"">
                        </a> -->
                    </div>
                    <div class="col-6 closenav mobmainmenu2">
                        <a href="javascript:void(0)" class="desktopclosebtn" onclick="closeNav()"
                            data-log="navbar-close">&times;</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <nav class="dd">
                            <ul class="headmenu_ul menuscroll" data-log="navbar-menu">
                                 <li class="headermenu">
                                    <div class="nodrop-box">
                                        <a class="nodrop-text" href="#exclusivity"
                                            data-log="navbar-offices">Exclusivity</a>
                                    </div>
                                </li>
                                <li class="headermenu">
                                    <div class="nodrop-box">
                                        <a class="nodrop-text" href="#overview" data-log="navbar-overview">Overview</a>
                                    </div>
                                </li>
                               
                                <li class="headermenu">
                                    <div class="nodrop-box">
                                        <a class="nodrop-text" href="#location"
                                            data-log="navbar-location">Location</a>
                                    </div>
                                </li>
                                <li class="headermenu">
                                    <div class="nodrop-box">
                                        <a class="nodrop-text" href="#contact-Us" data-log="navbar-contact">Contact
                                            Us</a>
                                    </div>
                                </li>
                            </ul>
                        </nav>
                        <!-- Enquire Now — mobile menu ke andar -->
                        <div class="mob-menu-enquire">
                            <a href="#contact-Us" class="enquire-nav-btn" onclick="closeNav()"
                                data-log="navbar-enquire-mobile">
                                Enquire Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== section Start =======================-->
    <div class="viewport">

        <div class="banner">
            <?php if ($mailsent !== true) { ?>
                <div class="content">
                    <div class="icon">
                        <i class="fa-solid fa fa-envelope"></i>
                    </div>
                    <h1>Oops!</h1>
                    <p>Sorry your request could not be processed!</p>
                    <div class="backhome">
                        <a href="index.html" class="">Go Back</a>
                    </div>
                </div>
            <?php } else { ?>
                <div class="content">
                    <div class="icon">
                        <i class="fa-solid fa fa-envelope"></i>
                    </div>
                    <h1>Thank You!</h1>
                    <p>
                        Thank you for expressing interest on our website.<br />
                        Our expert will get in touch with you shortly.<br />
                        <!-- You will be redirected in <span class="tiMer"></span> seconds -->
                    </p>
                    <div class="backhome">
                        <a href="index.html" class="">Go Back</a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <!-- ========================================================================= -->
    </div>
    <!--================== section End =======================-->
    <!-- ANIMATION SCRIPT -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/%40fancyapps/ui%404.0/dist/fancybox.umd.js"></script>
    <script src="https://unpkg.com/aos%403.0.0-beta.6/dist/aos.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="owl/jquery.min.js"></script>
    <script src="owl/owl.carousel.js"></script>
  
    <!--============= navbar =================-->
    <script>
        $(function () {
            $(".drop-box-44").click(function () {
                $("#ul44").fadeToggle();
            });

            $(".drop-box-44").on("click", function () {
                $(this).toggleClass("marked");
                $(".drop-text-44").toggleClass("marked1");
            });

            $(".drop-box-44").click(function () {
                $(".rotate44").toggleClass("down44");
            });
        });
    </script>

    <script>
        $(function () {
            $(".drop-box-33").click(function () {
                $("#ul33").fadeToggle();
            });

            $(".drop-box-33").on("click", function () {
                $(this).toggleClass("marked");
                $(".drop-text-33").toggleClass("marked1");
            });

            $(".drop-box-33").click(function () {
                $(".rotate33").toggleClass("down33");
            });
        });
    </script>

    <script>
        $(function () {
            $(".drop-box-22").click(function () {
                $("#ul22").fadeToggle();
            });

            $(".drop-box-22").on("click", function () {
                $(this).toggleClass("marked");
                $(".drop-text-22").toggleClass("marked1");
            });

            $(".drop-box-22").click(function () {
                $(".rotate22").toggleClass("down22");
            });
        });
    </script>

    <script>
        $(function () {
            $(".drop-box55").click(function () {
                $("#ul55").fadeToggle();
            });

            $(".drop-box55").on("click", function () {
                $(this).toggleClass("marked");
                $(".drop-text55").toggleClass("marked1");
            });

            $(".drop-box55").click(function () {
                $(".rotate55").toggleClass("down55");
            });
        });
    </script>

    <script>
        function openNav() {
            document.getElementById("myNav").style.width = "100%";
        }

        function closeNav() {
            document.getElementById("myNav").style.width = "0%";
        }

        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("#myNav a").forEach(function (link) {
                link.addEventListener("click", function () {
                    closeNav();
                });
            });
        });
    </script>
    <script>
        // Navbar transparent → white on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.head1');
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>

    <script>
        // Mobile menu open/close
        function openNav() {
            document.getElementById("myNav").style.display = "block";
            document.body.style.overflow = "hidden";
        }

        function closeNav() {
            document.getElementById("myNav").style.display = "none";
            document.body.style.overflow = "";
        }

        // Menu links pe click karne se bhi band ho
        document.querySelectorAll('#myNav .nodrop-text, #myNav .enquire-nav-btn').forEach(function(link) {
            link.addEventListener('click', function() {
                closeNav();
            });
        });

        // Navbar scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.head1');
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>




    <!--============= navbar =================-->

</body>

</html>
