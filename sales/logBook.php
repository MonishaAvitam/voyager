<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';


// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}

// check admin
$user_role = $_SESSION['user_role'];

function formatTimestamp($timestamp)
{
    // Convert timestamp to DateTime object
    $messageDate = new DateTime($timestamp);
    $today = new DateTime();
    $yesterday = new DateTime('yesterday');

    // Format the date to compare
    $messageDate->setTime(0, 0); // Remove time component for date comparison
    $today->setTime(0, 0);       // Remove time component for date comparison
    $yesterday->setTime(0, 0);   // Remove time component for date comparison

    // Determine if the date is today, yesterday, or another day
    if ($messageDate == $today) {
        return "Today";
    } elseif ($messageDate == $yesterday) {
        return "Yesterday";
    } else {
        return $messageDate->format('d M Y'); // Format for other dates
    }
}

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f4f7f6;
            height: 100vh;
            overflow: hidden;
        }

        .card {
            transition: 0.5s ease;
            border: 0;
            border-radius: 0.55rem;
            position: absolute;
            width: 100%;
            height: 100vh;
        }

        .chat-app .people-list {
            width: 380px;
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;

        }

        .chat-app .chat {
            margin-left: 380px;
            border-left: 1px solid #eaeaea;
        }

        .people-list {
            transition: 0.5s ease;
            margin-bottom: 100px;
            position: relative;
            /* Ensures that sticky positioning is relative to this container */


        }

        .people-list .chat-list li {
            padding: 10px 15px;
            list-style: none;
            border-radius: 3px;

        }

        .people-list .chat-list li:hover {
            background: #efefef;
            cursor: pointer;
        }

        .people-list .chat-list li.active {
            background: #efefef;
        }

        .people-list .chat-list li .name {
            font-size: 15px;
        }

        .people-list .chat-list img {
            width: 45px;
            border-radius: 50%;
        }

        .people-list img {
            float: left;
            border-radius: 50%;
        }

        .people-list .about {
            float: left;
            padding-left: 8px;
        }

        .people-list .status {
            color: #999;
            font-size: 13px;
        }

        .chat .chat-header {
            padding: 15px 20px;
            border-bottom: 2px solid #f4f7f6;
        }

        .chat .chat-header img {
            float: left;
            border-radius: 40px;
            width: 40px;
        }

        .chat .chat-header .chat-about {
            float: left;
            padding-left: 10px;
        }

        .chat .chat-history {
            padding: 10px;
            border-bottom: 2px solid #fff;
        }

        .chat .chat-history ul {
            padding: 0;
        }

        .chat .chat-history ul li {
            list-style: none;
        }

        .chat .chat-history ul li:last-child {
            margin-bottom: 100px;
        }

        .chat .chat-history .message-data {
            margin-bottom: 15px;
        }

        .chat .chat-history .message-data img {
            border-radius: 40px;
            width: 40px;
        }

        .chat .chat-history .message-data-time {
            color: #434651;
            padding-left: 6px;
        }

        .chat .chat-history .message {
            color: #444;
            padding: 18px 30px;
            line-height: 26px;
            font-size: 16px;
            text-align: justify;
            border-radius: 7px;
            display: inline-block;
            position: relative;
        }

        .chat .chat-history .message:after {
            bottom: 100%;
            left: 7%;
            border: solid transparent;
            content: " ";
            width: 0;
            position: absolute;
            pointer-events: none;
            border-bottom-color: #fff;
            border-width: 10px;
            margin-left: -10px;
        }

        .chat .chat-history .my-message {
            background: #efefef;
        }

        .chat .chat-history .my-message:after {
            bottom: 100%;
            left: 30px;
            border: solid transparent;
            position: absolute;
            pointer-events: none;
            border-bottom-color: #efefef;
            border-width: 10px;
            margin-left: -10px;
        }

        .chat .chat-history .other-message {
            background: #e8f1f3;
            text-align: justify;
        }

        .chat .chat-history .other-message:after {
            border-bottom-color: #e8f1f3;
            left: 70%;
        }



        .chat-message {
            position: fixed;
            bottom: 0;
            width: calc(100% - 380px);
            /* Adjust width based on the sidebar width */
            /* Background color for the input area */
            z-index: 10;
        }

        .chat-message .input-group-prepend {
            display: flex;
            align-items: center;
        }

        .chat-message .form-control {
            flex-grow: 1;
            border: none;
            border-radius: 5px;
            padding: 10px;
            margin-right: 10px;
        }

        .chat-message .btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .chat-message .btn:hover {
            background-color: #0056b3;
        }

        .chat-message .input-group-text {
            margin: 0;
            border: none;
            background: none;
        }


        /* Status Colors */
        .online {
            color: #86c541;
        }

        .offline {
            color: #e47297;
        }

        .me {
            color: #1d8ecd;
        }

        .float-right {
            float: right;
        }

        .clearfix:after {
            visibility: hidden;
            display: block;
            font-size: 0;
            content: " ";
            clear: both;
            height: 0;

        }

        /* Hide scrollbar for WebKit browsers */
        ::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: transparent;
        }

        /* Hide scrollbar for Firefox */
        html {
            scrollbar-width: none;
        }

        /* Hide scrollbar for IE and Edge */
        html {
            -ms-overflow-style: none;
        }

        @media (max-width: 767.98px) {
            /* Your mobile-specific styles go here */

            .people-list {
                overflow-y: auto;
                height: var(--people-list-height);
            }

            .chat {
                height: calc(100vh - 56px - var(--people-list-height));
            }

            .chat-history {
                height: 100%;
                overflow-y: auto;
            }

            .chat-message {
                position: fixed;
                bottom: 0;
                width: 100%;
                padding: 10px 20px;
                background-color: bla;
                z-index: 10;
            }

            .people-list-toggle {
                position: fixed;
                bottom: 60px;
                left: 0;
                right: 0;
                z-index: 10;
            }
        }
    </style>


</head>

<!-- NAV BAR  -->

<nav id="navbar" class="navbar navbar-expand navbar-light topbar sticky-top shadow" style="background-color: #070A19;">
    <a class="sidebar-brand text-decoration-none text-reset d-flex align-items-center justify-content-center" href="logBook.php">
        <img class="sidebar-brand-text mx-3" src="https://www.csaengineering.com.au/wp-content/uploads/2022/10/White-Logo.png" alt="CSA Engineering Logo" style="max-width: 100px; height: auto;">
        <h5 class="mt-2 font-italic font-weight-bolder text-light " style="letter-spacing: 2px;">Customer Log Book</h5>
    </a>


    <ul class="navbar-nav ml-auto">
        <form class="form-inline">
            <button id="people-list-toggle" class="btn btn-primary d-block d-sm-none">People</button>
        </form>



        <ul class="navbar-nav ml-auto ">

            <div class=" d-flex justify-content-center align-items-center ">
                <div class=" mr-3 ">
                    <div class="mt-2">
                        <style>
                            /* REMASTERED */
                            /* RTX-ON */
                            /* completely redone toggle and droid */

                            .bb8-toggle {
                                --toggle-size: 6px;
                                /* Adjust font size for smaller screens */
                                --toggle-width: 8em;
                                /* Adjust width */
                                --toggle-height: 2.5em;

                                --toggle-offset: calc((var(--toggle-height) - var(--bb8-diameter)) / 2);
                                --toggle-bg: linear-gradient(#2c4770, #070e2b 35%, #628cac 50% 70%, #a6c5d4) no-repeat;
                                --bb8-diameter: 4.375em;
                                --radius: 99em;
                                --transition: 0.4s;
                                --accent: #de7d2f;
                                --bb8-bg: #fff;
                            }


                            @media (min-width: 768px) {
                                .bb8-toggle {
                                    --toggle-size: 6px;
                                    /* Adjust font size for smaller screens */
                                    --toggle-width: 8em;
                                    /* Adjust width */
                                    --toggle-height: 2.5em;
                                    ;

                                }
                            }

                            /* Media query for smaller screens */
                            @media (max-width: 768px) {
                                .bb8-toggle {
                                    --toggle-size: 12px;
                                    /* Adjust font size for smaller screens */
                                    --toggle-width: 8em;
                                    /* Adjust width */
                                    --toggle-height: 2.5em;
                                    /* Adjust height */
                                    /* Adjust other styles as needed */
                                }
                            }

                            /* Media query for even smaller screens */
                            @media (max-width: 480px) {
                                .bb8-toggle {
                                    --toggle-size: 10px;
                                    /* Further reduce font size */
                                    --toggle-width: 6em;
                                    /* Further adjust width */
                                    --toggle-height: 2em;
                                    /* Further adjust height */
                                    /* Additional adjustments */
                                }
                            }

                            .bb8-toggle,
                            .bb8-toggle *,
                            .bb8-toggle *::before,
                            .bb8-toggle *::after {
                                -webkit-box-sizing: border-box;
                                box-sizing: border-box;
                            }

                            .bb8-toggle {
                                cursor: pointer;
                                margin-top: var(--margin-top-for-head);
                                font-size: var(--toggle-size);
                            }

                            .bb8-toggle__checkbox {
                                -webkit-appearance: none;
                                -moz-appearance: none;
                                appearance: none;
                                display: none;
                            }

                            .bb8-toggle__container {
                                width: var(--toggle-width);
                                height: var(--toggle-height);
                                background: var(--toggle-bg);
                                background-size: 100% 11.25em;
                                background-position-y: -5.625em;
                                border-radius: var(--radius);
                                position: relative;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8 {
                                display: -webkit-box;
                                display: -ms-flexbox;
                                display: flex;
                                -webkit-box-orient: vertical;
                                -webkit-box-direction: normal;
                                -ms-flex-direction: column;
                                flex-direction: column;
                                -webkit-box-align: center;
                                -ms-flex-align: center;
                                align-items: center;
                                position: absolute;
                                top: calc(var(--toggle-offset) - 1.688em + 0.188em);
                                left: var(--toggle-offset);
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                                z-index: 2;
                            }

                            .bb8__head-container {
                                position: relative;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                                z-index: 2;
                                -webkit-transform-origin: 1.25em 3.75em;
                                -ms-transform-origin: 1.25em 3.75em;
                                transform-origin: 1.25em 3.75em;
                            }

                            .bb8__head {
                                overflow: hidden;
                                margin-bottom: -0.188em;
                                width: 2.5em;
                                height: 1.688em;
                                background: -o-linear-gradient(transparent 0.063em,
                                        dimgray 0.063em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.5em,
                                        transparent 0.5em 1.313em,
                                        silver 1.313em 1.438em,
                                        transparent 1.438em),
                                    -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
                                background: -o-linear-gradient(transparent 0.063em,
                                        dimgray 0.063em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.5em,
                                        transparent 0.5em 1.313em,
                                        silver 1.313em 1.438em,
                                        transparent 1.438em),
                                    -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
                                background: -o-linear-gradient(transparent 0.063em,
                                        dimgray 0.063em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.5em,
                                        transparent 0.5em 1.313em,
                                        silver 1.313em 1.438em,
                                        transparent 1.438em),
                                    -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
                                background: -o-linear-gradient(transparent 0.063em,
                                        dimgray 0.063em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.5em,
                                        transparent 0.5em 1.313em,
                                        silver 1.313em 1.438em,
                                        transparent 1.438em),
                                    -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
                                    -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
                                background: linear-gradient(transparent 0.063em,
                                        dimgray 0.063em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.5em,
                                        transparent 0.5em 1.313em,
                                        silver 1.313em 1.438em,
                                        transparent 1.438em),
                                    linear-gradient(45deg,
                                        transparent 0.188em,
                                        var(--bb8-bg) 0.188em 1.25em,
                                        transparent 1.25em),
                                    linear-gradient(-45deg,
                                        transparent 0.188em,
                                        var(--bb8-bg) 0.188em 1.25em,
                                        transparent 1.25em),
                                    linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
                                border-radius: var(--radius) var(--radius) 0 0;
                                position: relative;
                                z-index: 1;
                                -webkit-filter: drop-shadow(0 0.063em 0.125em gray);
                                filter: drop-shadow(0 0.063em 0.125em gray);
                            }

                            .bb8__head::before {
                                content: "";
                                position: absolute;
                                width: 0.563em;
                                height: 0.563em;
                                background: -o-radial-gradient(0.25em 0.375em,
                                        0.125em circle,
                                        red,
                                        transparent),
                                    -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
                                    -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
                                background: -o-radial-gradient(0.25em 0.375em,
                                        0.125em circle,
                                        red,
                                        transparent),
                                    -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
                                    -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
                                background: -o-radial-gradient(0.25em 0.375em,
                                        0.125em circle,
                                        red,
                                        transparent),
                                    -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
                                    -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
                                background: -o-radial-gradient(0.25em 0.375em,
                                        0.125em circle,
                                        red,
                                        transparent),
                                    -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
                                    -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
                                background: radial-gradient(0.125em circle at 0.25em 0.375em,
                                        red,
                                        transparent),
                                    radial-gradient(0.063em circle at 0.375em 0.188em,
                                        var(--bb8-bg) 50%,
                                        transparent 100%),
                                    linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
                                border-radius: var(--radius);
                                top: 0.413em;
                                left: 50%;
                                -webkit-transform: translate(-50%);
                                -ms-transform: translate(-50%);
                                transform: translate(-50%);
                                -webkit-box-shadow: 0 0 0 0.089em lightgray, 0.563em 0.281em 0 -0.148em,
                                    0.563em 0.281em 0 -0.1em var(--bb8-bg), 0.563em 0.281em 0 -0.063em;
                                box-shadow: 0 0 0 0.089em lightgray, 0.563em 0.281em 0 -0.148em,
                                    0.563em 0.281em 0 -0.1em var(--bb8-bg), 0.563em 0.281em 0 -0.063em;
                                z-index: 1;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8__head::after {
                                content: "";
                                position: absolute;
                                bottom: 0.375em;
                                left: 0;
                                width: 100%;
                                height: 0.188em;
                                background: -o-linear-gradient(left,
                                        var(--accent) 0.125em,
                                        transparent 0.125em 0.188em,
                                        var(--accent) 0.188em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.938em,
                                        transparent 0.938em 1em,
                                        var(--accent) 1em 1.125em,
                                        transparent 1.125em 1.875em,
                                        var(--accent) 1.875em 2em,
                                        transparent 2em 2.063em,
                                        var(--accent) 2.063em 2.25em,
                                        transparent 2.25em 2.313em,
                                        var(--accent) 2.313em 2.375em,
                                        transparent 2.375em 2.438em,
                                        var(--accent) 2.438em);
                                background: -webkit-gradient(linear,
                                        left top,
                                        right top,
                                        color-stop(0.125em, var(--accent)),
                                        color-stop(0.125em, transparent),
                                        color-stop(0.188em, var(--accent)),
                                        color-stop(0.313em, transparent),
                                        color-stop(0.375em, var(--accent)),
                                        color-stop(0.938em, transparent),
                                        color-stop(1em, var(--accent)),
                                        color-stop(1.125em, transparent),
                                        color-stop(1.875em, var(--accent)),
                                        color-stop(2em, transparent),
                                        color-stop(2.063em, var(--accent)),
                                        color-stop(2.25em, transparent),
                                        color-stop(2.313em, var(--accent)),
                                        color-stop(2.375em, transparent),
                                        color-stop(2.438em, var(--accent)));
                                background: linear-gradient(to right,
                                        var(--accent) 0.125em,
                                        transparent 0.125em 0.188em,
                                        var(--accent) 0.188em 0.313em,
                                        transparent 0.313em 0.375em,
                                        var(--accent) 0.375em 0.938em,
                                        transparent 0.938em 1em,
                                        var(--accent) 1em 1.125em,
                                        transparent 1.125em 1.875em,
                                        var(--accent) 1.875em 2em,
                                        transparent 2em 2.063em,
                                        var(--accent) 2.063em 2.25em,
                                        transparent 2.25em 2.313em,
                                        var(--accent) 2.313em 2.375em,
                                        transparent 2.375em 2.438em,
                                        var(--accent) 2.438em);
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8__antenna {
                                position: absolute;
                                -webkit-transform: translateY(-90%);
                                -ms-transform: translateY(-90%);
                                transform: translateY(-90%);
                                width: 0.059em;
                                border-radius: var(--radius) var(--radius) 0 0;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8__antenna:nth-child(1) {
                                height: 0.938em;
                                right: 0.938em;
                                background: -o-linear-gradient(#000 0.188em, silver 0.188em);
                                background: -webkit-gradient(linear,
                                        left top,
                                        left bottom,
                                        color-stop(0.188em, #000),
                                        color-stop(0.188em, silver));
                                background: linear-gradient(#000 0.188em, silver 0.188em);
                            }

                            .bb8__antenna:nth-child(2) {
                                height: 0.375em;
                                left: 50%;
                                -webkit-transform: translate(-50%, -90%);
                                -ms-transform: translate(-50%, -90%);
                                transform: translate(-50%, -90%);
                                background: silver;
                            }

                            .bb8__body {
                                width: 4.375em;
                                height: 4.375em;
                                background: var(--bb8-bg);
                                border-radius: var(--radius);
                                position: relative;
                                overflow: hidden;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                                z-index: 1;
                                -webkit-transform: rotate(45deg);
                                -ms-transform: rotate(45deg);
                                transform: rotate(45deg);
                                background: -webkit-gradient(linear,
                                        right top,
                                        left top,
                                        color-stop(4%, var(--bb8-bg)),
                                        color-stop(4%, var(--accent)),
                                        color-stop(10%, transparent),
                                        color-stop(90%, var(--accent)),
                                        color-stop(96%, var(--bb8-bg))),
                                    -webkit-gradient(linear, left top, left bottom, color-stop(4%, var(--bb8-bg)), color-stop(4%, var(--accent)), color-stop(10%, transparent), color-stop(90%, var(--accent)), color-stop(96%, var(--bb8-bg))),
                                    -webkit-gradient(linear, left top, right top, color-stop(2.156em, transparent), color-stop(2.156em, silver), color-stop(2.188em, transparent)),
                                    -webkit-gradient(linear, left top, left bottom, color-stop(2.156em, transparent), color-stop(2.156em, silver), color-stop(2.188em, transparent));
                                background: -o-linear-gradient(right,
                                        var(--bb8-bg) 4%,
                                        var(--accent) 4% 10%,
                                        transparent 10% 90%,
                                        var(--accent) 90% 96%,
                                        var(--bb8-bg) 96%),
                                    -o-linear-gradient(var(--bb8-bg) 4%, var(--accent) 4% 10%, transparent 10% 90%, var(--accent) 90% 96%, var(--bb8-bg) 96%),
                                    -o-linear-gradient(left, transparent 2.156em, silver 2.156em 2.219em, transparent 2.188em),
                                    -o-linear-gradient(transparent 2.156em, silver 2.156em 2.219em, transparent 2.188em);
                                background: linear-gradient(-90deg,
                                        var(--bb8-bg) 4%,
                                        var(--accent) 4% 10%,
                                        transparent 10% 90%,
                                        var(--accent) 90% 96%,
                                        var(--bb8-bg) 96%),
                                    linear-gradient(var(--bb8-bg) 4%,
                                        var(--accent) 4% 10%,
                                        transparent 10% 90%,
                                        var(--accent) 90% 96%,
                                        var(--bb8-bg) 96%),
                                    linear-gradient(to right,
                                        transparent 2.156em,
                                        silver 2.156em 2.219em,
                                        transparent 2.188em),
                                    linear-gradient(transparent 2.156em,
                                        silver 2.156em 2.219em,
                                        transparent 2.188em);
                                background-color: var(--bb8-bg);
                            }

                            .bb8__body::after {
                                content: "";
                                bottom: 1.5em;
                                left: 0.563em;
                                position: absolute;
                                width: 0.188em;
                                height: 0.188em;
                                background: rgb(236, 236, 236);
                                color: rgb(236, 236, 236);
                                border-radius: 50%;
                                -webkit-box-shadow: 0.875em 0.938em, 0 -1.25em, 0.875em -2.125em,
                                    2.125em -2.125em, 3.063em -1.25em, 3.063em 0, 2.125em 0.938em;
                                box-shadow: 0.875em 0.938em, 0 -1.25em, 0.875em -2.125em, 2.125em -2.125em,
                                    3.063em -1.25em, 3.063em 0, 2.125em 0.938em;
                            }

                            .bb8__body::before {
                                content: "";
                                width: 2.625em;
                                height: 2.625em;
                                position: absolute;
                                border-radius: 50%;
                                z-index: 0.1;
                                overflow: hidden;
                                top: 50%;
                                left: 50%;
                                -webkit-transform: translate(-50%, -50%);
                                -ms-transform: translate(-50%, -50%);
                                transform: translate(-50%, -50%);
                                border: 0.313em solid var(--accent);
                                background: -o-radial-gradient(center,
                                        1em circle,
                                        rgb(236, 236, 236) 50%,
                                        transparent 51%),
                                    -o-radial-gradient(center, 1.25em circle, var(--bb8-bg) 50%, transparent 51%),
                                    -o-linear-gradient(right, transparent 42%, var(--accent) 42% 58%, transparent 58%),
                                    -o-linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
                                background: -o-radial-gradient(center,
                                        1em circle,
                                        rgb(236, 236, 236) 50%,
                                        transparent 51%),
                                    -o-radial-gradient(center, 1.25em circle, var(--bb8-bg) 50%, transparent 51%),
                                    -o-linear-gradient(right, transparent 42%, var(--accent) 42% 58%, transparent 58%),
                                    -o-linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
                                background: radial-gradient(1em circle at center,
                                        rgb(236, 236, 236) 50%,
                                        transparent 51%),
                                    radial-gradient(1.25em circle at center, var(--bb8-bg) 50%, transparent 51%),
                                    -webkit-gradient(linear, right top, left top, color-stop(42%, transparent), color-stop(42%, var(--accent)), color-stop(58%, transparent)),
                                    -webkit-gradient(linear, left top, left bottom, color-stop(42%, var(--bb8-bg)), color-stop(42%, var(--accent)), color-stop(58%, var(--bb8-bg)));
                                background: radial-gradient(1em circle at center,
                                        rgb(236, 236, 236) 50%,
                                        transparent 51%),
                                    radial-gradient(1.25em circle at center, var(--bb8-bg) 50%, transparent 51%),
                                    linear-gradient(-90deg,
                                        transparent 42%,
                                        var(--accent) 42% 58%,
                                        transparent 58%),
                                    linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
                            }

                            .artificial__hidden {
                                position: absolute;
                                border-radius: inherit;
                                inset: 0;
                                pointer-events: none;
                                overflow: hidden;
                            }

                            .bb8__shadow {
                                content: "";
                                width: var(--bb8-diameter);
                                height: 20%;
                                border-radius: 50%;
                                background: #3a271c;
                                -webkit-box-shadow: 0.313em 0 3.125em #3a271c;
                                box-shadow: 0.313em 0 3.125em #3a271c;
                                opacity: 0.25;
                                position: absolute;
                                bottom: 0;
                                left: calc(var(--toggle-offset) - 0.938em);
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                                -webkit-transform: skew(-70deg);
                                -ms-transform: skew(-70deg);
                                transform: skew(-70deg);
                                z-index: 1;
                            }

                            .bb8-toggle__scenery {
                                width: 100%;
                                height: 100%;
                                pointer-events: none;
                                overflow: hidden;
                                position: relative;
                                border-radius: inherit;
                            }

                            .bb8-toggle__scenery::before {
                                content: "";
                                position: absolute;
                                width: 100%;
                                height: 30%;
                                bottom: 0;
                                background: #b18d71;
                                z-index: 1;
                            }

                            .bb8-toggle__cloud {
                                z-index: 1;
                                position: absolute;
                                border-radius: 50%;
                            }

                            .bb8-toggle__cloud:nth-last-child(1) {
                                width: 0.875em;
                                height: 0.625em;
                                -webkit-filter: blur(0.125em) drop-shadow(0.313em 0.313em #ffffffae) drop-shadow(-0.625em 0 #fff) drop-shadow(-0.938em -0.125em #fff);
                                filter: blur(0.125em) drop-shadow(0.313em 0.313em #ffffffae) drop-shadow(-0.625em 0 #fff) drop-shadow(-0.938em -0.125em #fff);
                                right: 1.875em;
                                top: 2.813em;
                                background: -o-linear-gradient(bottom left, #ffffffae, #ffffffae);
                                background: -webkit-gradient(linear,
                                        left bottom,
                                        right top,
                                        from(#ffffffae),
                                        to(#ffffffae));
                                background: linear-gradient(to top right, #ffffffae, #ffffffae);
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8-toggle__cloud:nth-last-child(2) {
                                top: 0.625em;
                                right: 4.375em;
                                width: 0.875em;
                                height: 0.375em;
                                background: #dfdedeae;
                                -webkit-filter: blur(0.125em) drop-shadow(-0.313em -0.188em #e0dfdfae) drop-shadow(-0.625em -0.188em #bbbbbbae) drop-shadow(-1em 0.063em #cfcfcfae);
                                filter: blur(0.125em) drop-shadow(-0.313em -0.188em #e0dfdfae) drop-shadow(-0.625em -0.188em #bbbbbbae) drop-shadow(-1em 0.063em #cfcfcfae);
                                -webkit-transition: 0.6s;
                                -o-transition: 0.6s;
                                transition: 0.6s;
                            }

                            .bb8-toggle__cloud:nth-last-child(3) {
                                top: 1.25em;
                                right: 0.938em;
                                width: 0.875em;
                                height: 0.375em;
                                background: #ffffffae;
                                -webkit-filter: blur(0.125em) drop-shadow(0.438em 0.188em #ffffffae) drop-shadow(-0.625em 0.313em #ffffffae);
                                filter: blur(0.125em) drop-shadow(0.438em 0.188em #ffffffae) drop-shadow(-0.625em 0.313em #ffffffae);
                                -webkit-transition: 0.8s;
                                -o-transition: 0.8s;
                                transition: 0.8s;
                            }

                            .gomrassen,
                            .hermes,
                            .chenini {
                                position: absolute;
                                border-radius: var(--radius);
                                background: -o-linear-gradient(#fff, #6e8ea2);
                                background: -webkit-gradient(linear,
                                        left top,
                                        left bottom,
                                        from(#fff),
                                        to(#6e8ea2));
                                background: linear-gradient(#fff, #6e8ea2);
                                top: 100%;
                            }

                            .gomrassen {
                                left: 0.938em;
                                width: 1.875em;
                                height: 1.875em;
                                -webkit-box-shadow: 0 0 0.188em #ffffff52, 0 0 0.188em #6e8ea24b;
                                box-shadow: 0 0 0.188em #ffffff52, 0 0 0.188em #6e8ea24b;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .gomrassen::before,
                            .gomrassen::after {
                                content: "";
                                position: absolute;
                                border-radius: inherit;
                                -webkit-box-shadow: inset 0 0 0.063em rgb(140, 162, 169);
                                box-shadow: inset 0 0 0.063em rgb(140, 162, 169);
                                background: rgb(184, 196, 200);
                            }

                            .gomrassen::before {
                                left: 0.313em;
                                top: 0.313em;
                                width: 0.438em;
                                height: 0.438em;
                            }

                            .gomrassen::after {
                                width: 0.25em;
                                height: 0.25em;
                                left: 1.25em;
                                top: 0.75em;
                            }

                            .hermes {
                                left: 3.438em;
                                width: 0.625em;
                                height: 0.625em;
                                -webkit-box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
                                box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
                                -webkit-transition: 0.6s;
                                -o-transition: 0.6s;
                                transition: 0.6s;
                            }

                            .chenini {
                                left: 4.375em;
                                width: 0.5em;
                                height: 0.5em;
                                -webkit-box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
                                box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
                                -webkit-transition: 0.8s;
                                -o-transition: 0.8s;
                                transition: 0.8s;
                            }

                            .tatto-1,
                            .tatto-2 {
                                position: absolute;
                                width: 1.25em;
                                height: 1.25em;
                                border-radius: var(--radius);
                            }

                            .tatto-1 {
                                background: #fefefe;
                                right: 3.125em;
                                top: 0.625em;
                                -webkit-box-shadow: 0 0 0.438em #fdf4e1;
                                box-shadow: 0 0 0.438em #fdf4e1;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .tatto-2 {
                                background: -o-linear-gradient(#e6ac5c, #d75449);
                                background: -webkit-gradient(linear,
                                        left top,
                                        left bottom,
                                        from(#e6ac5c),
                                        to(#d75449));
                                background: linear-gradient(#e6ac5c, #d75449);
                                right: 1.25em;
                                top: 2.188em;
                                -webkit-box-shadow: 0 0 0.438em #e6ad5c3d, 0 0 0.438em #d755494f;
                                box-shadow: 0 0 0.438em #e6ad5c3d, 0 0 0.438em #d755494f;
                                -webkit-transition: 0.7s;
                                -o-transition: 0.7s;
                                transition: 0.7s;
                            }

                            .bb8-toggle__star {
                                position: absolute;
                                width: 0.063em;
                                height: 0.063em;
                                background: #fff;
                                border-radius: var(--radius);
                                -webkit-filter: drop-shadow(0 0 0.063em #fff);
                                filter: drop-shadow(0 0 0.063em #fff);
                                color: #fff;
                                top: 100%;
                            }

                            .bb8-toggle__star:nth-child(1) {
                                left: 3.75em;
                                -webkit-box-shadow: 1.25em 0.938em, -1.25em 2.5em, 0 1.25em, 1.875em 0.625em,
                                    -3.125em 1.875em, 1.25em 2.813em;
                                box-shadow: 1.25em 0.938em, -1.25em 2.5em, 0 1.25em, 1.875em 0.625em,
                                    -3.125em 1.875em, 1.25em 2.813em;
                                -webkit-transition: 0.2s;
                                -o-transition: 0.2s;
                                transition: 0.2s;
                            }

                            .bb8-toggle__star:nth-child(2) {
                                left: 4.688em;
                                -webkit-box-shadow: 0.625em 0, 0 0.625em, -0.625em -0.625em, 0.625em 0.938em,
                                    -3.125em 1.25em, 1.25em -1.563em;
                                box-shadow: 0.625em 0, 0 0.625em, -0.625em -0.625em, 0.625em 0.938em,
                                    -3.125em 1.25em, 1.25em -1.563em;
                                -webkit-transition: 0.3s;
                                -o-transition: 0.3s;
                                transition: 0.3s;
                            }

                            .bb8-toggle__star:nth-child(3) {
                                left: 5.313em;
                                -webkit-box-shadow: -0.625em -0.625em, -2.188em 1.25em, -2.188em 0,
                                    -3.75em -0.625em, -3.125em -0.625em, -2.5em -0.313em, 0.75em -0.625em;
                                box-shadow: -0.625em -0.625em, -2.188em 1.25em, -2.188em 0, -3.75em -0.625em,
                                    -3.125em -0.625em, -2.5em -0.313em, 0.75em -0.625em;
                                -webkit-transition: var(--transition);
                                -o-transition: var(--transition);
                                transition: var(--transition);
                            }

                            .bb8-toggle__star:nth-child(4) {
                                left: 1.875em;
                                width: 0.125em;
                                height: 0.125em;
                                -webkit-transition: 0.5s;
                                -o-transition: 0.5s;
                                transition: 0.5s;
                            }

                            .bb8-toggle__star:nth-child(5) {
                                left: 5em;
                                width: 0.125em;
                                height: 0.125em;
                                -webkit-transition: 0.6s;
                                -o-transition: 0.6s;
                                transition: 0.6s;
                            }

                            .bb8-toggle__star:nth-child(6) {
                                left: 2.5em;
                                width: 0.125em;
                                height: 0.125em;
                                -webkit-transition: 0.7s;
                                -o-transition: 0.7s;
                                transition: 0.7s;
                            }

                            .bb8-toggle__star:nth-child(7) {
                                left: 3.438em;
                                width: 0.125em;
                                height: 0.125em;
                                -webkit-transition: 0.8s;
                                -o-transition: 0.8s;
                                transition: 0.8s;
                            }

                            /* actions */

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(1) {
                                top: 0.625em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(2) {
                                top: 1.875em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(3) {
                                top: 1.25em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(4) {
                                top: 3.438em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(5) {
                                top: 3.438em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(6) {
                                top: 0.313em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(7) {
                                top: 1.875em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__cloud {
                                right: -100%;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .gomrassen {
                                top: 0.938em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .hermes {
                                top: 2.5em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .chenini {
                                top: 2.75em;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container {
                                background-position-y: 0;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .tatto-1 {
                                top: 100%;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .tatto-2 {
                                top: 100%;
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8 {
                                left: calc(100% - var(--bb8-diameter) - var(--toggle-offset));
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8__shadow {
                                left: calc(100% - var(--bb8-diameter) - var(--toggle-offset) + 0.938em);
                                -webkit-transform: skew(70deg);
                                -ms-transform: skew(70deg);
                                transform: skew(70deg);
                            }

                            .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8__body {
                                -webkit-transform: rotate(180deg);
                                -ms-transform: rotate(180deg);
                                transform: rotate(225deg);
                            }

                            .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__head::before {
                                left: 100%;
                            }

                            .bb8-toggle__checkbox:not(:checked):hover+.bb8-toggle__container .bb8__antenna:nth-child(1) {
                                right: 1.5em;
                            }

                            .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__antenna:nth-child(2) {
                                left: 0.938em;
                            }

                            .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__head::after {
                                background-position: 1.375em 0;
                            }

                            .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__head::before {
                                left: 0;
                            }

                            .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__antenna:nth-child(2) {
                                left: calc(100% - 0.938em);
                            }

                            .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__head::after {
                                background-position: -1.375em 0;
                            }

                            .bb8-toggle__checkbox:active+.bb8-toggle__container .bb8__head-container {
                                -webkit-transform: rotate(25deg);
                                -ms-transform: rotate(25deg);
                                transform: rotate(25deg);
                            }

                            .bb8-toggle__checkbox:checked:active+.bb8-toggle__container .bb8__head-container {
                                -webkit-transform: rotate(-25deg);
                                -ms-transform: rotate(-25deg);
                                transform: rotate(-25deg);
                            }

                            .bb8:hover .bb8__head::before,
                            .bb8:hover .bb8__antenna:nth-child(2) {
                                left: 50% !important;
                            }

                            .bb8:hover .bb8__antenna:nth-child(1) {
                                right: 0.938em !important;
                            }

                            .bb8:hover .bb8__head::after {
                                background-position: 0 0 !important;
                            }
                        </style>
                        <label class="bb8-toggle  " id="themeSwitchBtn">

                            <input class="bb8-toggle__checkbox" id="themeState" type="checkbox">



                            <div class="bb8-toggle__container">
                                <div class="bb8-toggle__scenery">
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="bb8-toggle__star"></div>
                                    <div class="tatto-1"></div>
                                    <div class="tatto-2"></div>
                                    <div class="gomrassen"></div>
                                    <div class="hermes"></div>
                                    <div class="chenini"></div>
                                    <div class="bb8-toggle__cloud"></div>
                                    <div class="bb8-toggle__cloud"></div>
                                    <div class="bb8-toggle__cloud"></div>
                                </div>
                                <div class="bb8">
                                    <div class="bb8__head-container">
                                        <div class="bb8__antenna"></div>
                                        <div class="bb8__antenna"></div>
                                        <div class="bb8__head"></div>
                                    </div>
                                    <div class="bb8__body"></div>
                                </div>
                                <div class="artificial__hidden">
                                    <div class="bb8__shadow"></div>
                                </div>
                            </div>
                        </label>


                        <script>
                            const themeStateBtn = document.getElementById('themeState');

                            // Check the stored theme preference on page load
                            const storedTheme = sessionStorage.getItem('theme') ? sessionStorage.getItem('theme') : 'light-mode';
                            if (storedTheme === 'light-mode') {
                                document.body.classList.remove('dark-mode');
                                document.body.classList.add('light-mode');
                            } else {
                                document.body.classList.add('dark-mode');
                                document.body.classList.remove('light-mode');
                                themeStateBtn.checked = true;
                            }

                            // Add event listener for theme toggle
                            themeStateBtn.addEventListener('change', (e) => {
                                if (e.target.checked) {
                                    // Apply dark theme
                                    sessionStorage.setItem('theme', 'dark-mode');
                                } else {
                                    // Apply light theme
                                    sessionStorage.setItem('theme', 'light-mode');
                                }

                                // Reload the page to apply the theme
                                window.location.reload();
                            });
                        </script>

                    </div>
                </div>
                <div>
                    <a href="sales_dashboard.php" class="btn btn-outline-warning btn-sm float-right">Switch To OBI</a>
                </div>
            </div>

            <div class="topbar-divider d-none d-sm-block"></div>

            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($user_name); ?></span>
                    <img class="img-profile rounded-circle" src="img/undraw_profile.svg" alt="User Profile">
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                    <a class="dropdown-item">Your User ID Is <?php echo htmlspecialchars($user_id); ?></a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </ul>
</nav>

<!-- loader -->
<div id="loaderFrame" class="d-none">
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh;" hidden>
        <style>
            /* From Uiverse.io by Nawsome */
            .loader {
                --background: linear-gradient(135deg, #23C4F8, #275EFE);
                --shadow: rgba(39, 94, 254, 0.28);
                --text: #6C7486;
                --page: rgba(255, 255, 255, 0.36);
                --page-fold: rgba(255, 255, 255, 0.52);
                --duration: 3s;
                width: 200px;
                height: 140px;
                position: relative;
            }

            .loader:before,
            .loader:after {
                --r: -6deg;
                content: "";
                position: absolute;
                bottom: 8px;
                width: 120px;
                top: 80%;
                box-shadow: 0 16px 12px var(--shadow);
                transform: rotate(var(--r));
            }

            .loader:before {
                left: 4px;
            }

            .loader:after {
                --r: 6deg;
                right: 4px;
            }

            .loader div {
                width: 100%;
                height: 100%;
                border-radius: 13px;
                position: relative;
                z-index: 1;
                perspective: 600px;
                box-shadow: 0 4px 6px var(--shadow);
                background-image: var(--background);
            }

            .loader div ul {
                margin: 0;
                padding: 0;
                list-style: none;
                position: relative;
            }

            .loader div ul li {
                --r: 180deg;
                --o: 0;
                --c: var(--page);
                position: absolute;
                top: 10px;
                left: 10px;
                transform-origin: 100% 50%;
                color: var(--c);
                opacity: var(--o);
                transform: rotateY(var(--r));
                -webkit-animation: var(--duration) ease infinite;
                animation: var(--duration) ease infinite;
            }

            .loader div ul li:nth-child(2) {
                --c: var(--page-fold);
                -webkit-animation-name: page-2;
                animation-name: page-2;
            }

            .loader div ul li:nth-child(3) {
                --c: var(--page-fold);
                -webkit-animation-name: page-3;
                animation-name: page-3;
            }

            .loader div ul li:nth-child(4) {
                --c: var(--page-fold);
                -webkit-animation-name: page-4;
                animation-name: page-4;
            }

            .loader div ul li:nth-child(5) {
                --c: var(--page-fold);
                -webkit-animation-name: page-5;
                animation-name: page-5;
            }

            .loader div ul li svg {
                width: 90px;
                height: 120px;
                display: block;
            }

            .loader div ul li:first-child {
                --r: 0deg;
                --o: 1;
            }

            .loader div ul li:last-child {
                --o: 1;
            }

            .loader span {
                display: block;
                left: 0;
                right: 0;
                top: 100%;
                margin-top: 20px;
                text-align: center;
                color: var(--text);
            }

            @keyframes page-2 {
                0% {
                    transform: rotateY(180deg);
                    opacity: 0;
                }

                20% {
                    opacity: 1;
                }

                35%,
                100% {
                    opacity: 0;
                }

                50%,
                100% {
                    transform: rotateY(0deg);
                }
            }

            @keyframes page-3 {
                15% {
                    transform: rotateY(180deg);
                    opacity: 0;
                }

                35% {
                    opacity: 1;
                }

                50%,
                100% {
                    opacity: 0;
                }

                65%,
                100% {
                    transform: rotateY(0deg);
                }
            }

            @keyframes page-4 {
                30% {
                    transform: rotateY(180deg);
                    opacity: 0;
                }

                50% {
                    opacity: 1;
                }

                65%,
                100% {
                    opacity: 0;
                }

                80%,
                100% {
                    transform: rotateY(0deg);
                }
            }

            @keyframes page-5 {
                45% {
                    transform: rotateY(180deg);
                    opacity: 0;
                }

                65% {
                    opacity: 1;
                }

                80%,
                100% {
                    opacity: 0;
                }

                95%,
                100% {
                    transform: rotateY(0deg);
                }
            }
        </style>
        <div class="loader">
            <div>
                <ul>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                    <li>
                        <svg fill="currentColor" viewBox="0 0 90 120">
                            <path d="M90,0 L90,120 L11,120 C4.92486775,120 0,115.075132 0,109 L0,11 C0,4.92486775 4.92486775,0 11,0 L90,0 Z M71.5,81 L18.5,81 C17.1192881,81 16,82.1192881 16,83.5 C16,84.8254834 17.0315359,85.9100387 18.3356243,85.9946823 L18.5,86 L71.5,86 C72.8807119,86 74,84.8807119 74,83.5 C74,82.1745166 72.9684641,81.0899613 71.6643757,81.0053177 L71.5,81 Z M71.5,57 L18.5,57 C17.1192881,57 16,58.1192881 16,59.5 C16,60.8254834 17.0315359,61.9100387 18.3356243,61.9946823 L18.5,62 L71.5,62 C72.8807119,62 74,60.8807119 74,59.5 C74,58.1192881 72.8807119,57 71.5,57 Z M71.5,33 L18.5,33 C17.1192881,33 16,34.1192881 16,35.5 C16,36.8254834 17.0315359,37.9100387 18.3356243,37.9946823 L18.5,38 L71.5,38 C72.8807119,38 74,36.8807119 74,35.5 C74,34.1192881 72.8807119,33 71.5,33 Z"></path>
                        </svg>
                    </li>
                </ul>
            </div><span>Loading</span>
            <p class="text-center">CSA Customer LogBook</p>
        </div>
    </div>
</div>


<!-- MAIN FRAME -->
<div id="mainFrame" class="d-block">




    <!-- MAIN CONTENT -->

    <div>
        <div class="">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <div class="card chat-app rounded-0">
                        <div id="plist" class="people-list " style="max-height: 90vh;">
                            <div class="input-group ">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                            </div>
                            <ul id="itemList" class="list-unstyled chat-list mt-2 ">
                                <?php
                                $contacts = "
                                    SELECT DISTINCT 
                                        c.customer_name,
                                        c.contact_id,
                                        MAX(lb.timestamp) AS last_timestamp
                                    FROM contacts c
                                    LEFT JOIN customer_logBook lb 
                                    ON c.contact_id = lb.contact_id
                                    GROUP BY c.customer_name, c.contact_id
                                    ORDER BY last_timestamp DESC
                                ";

                                $contactsResult = $conn->query($contacts);

                                if ($contactsResult) {
                                    if ($contactsResult->num_rows > 0) {
                                        while ($row = $contactsResult->fetch_assoc()) {
                                ?>
                                            <a href="?contact_id=<?php echo htmlspecialchars($row['contact_id']); ?>">
                                                <li class="clearfix">
                                                    <img src="https://bootdey.com/img/Content/avatar/avatar2.png" alt="Avatar">
                                                    <div class="about">
                                                        <div class="name"><?php echo htmlspecialchars((strlen($row['customer_name']) > 10) ? substr($row['customer_name'], 0, 25) . '...' : $row['customer_name']); ?></div>
                                                        <div class="status">
                                                            <i class="fa fa-circle <?php echo $row['last_timestamp'] ? 'online' : 'offline'; ?>"></i>
                                                            <?php echo $row['last_timestamp'] ? 'Last updated ' . formatTimestamp($row['last_timestamp']) : ''; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            </a>
                                <?php
                                        }
                                    } else {
                                        echo "<li>No contacts found.</li>";
                                    }
                                    $contactsResult->close();
                                } else {
                                    echo "<li>Error fetching contacts.</li>";
                                }
                                ?>
                            </ul>
                            <script>
                                document.getElementById('searchInput').addEventListener('keyup', function() {
                                    var input = this.value.toLowerCase();
                                    var items = document.querySelectorAll('#itemList li');

                                    items.forEach(function(item) {
                                        if (item.textContent.toLowerCase().includes(input)) {
                                            item.style.display = '';
                                        } else {
                                            item.style.display = 'none';
                                        }
                                    });
                                });
                            </script>

                        </div>



                        <?php
                        $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
                        if ($contact_id > 0) {
                            $contactsQuery = "SELECT c.* FROM contacts c WHERE c.contact_id = $contact_id";
                            $contactsResult = $conn->query($contactsQuery);

                            if ($contactsResult && $contactsResult->num_rows > 0) {
                                $row = $contactsResult->fetch_assoc();
                        ?>
                                <div class="chat">
                                    <div class="chat-header clearfix card-header  ">
                                        <div class="row ">
                                            <div class="col-lg-6 d-flex align-items-center ">
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#view_info">
                                                    <img src="https://bootdey.com/img/Content/avatar/avatar2.png" alt="Avatar">
                                                </a>
                                                <div class="chat-about ">
                                                    <h6 class="m-b-0"><?php echo htmlspecialchars($row['customer_name']); ?></h6>
                                                </div>

                                            </div>
                                            <div class="col-lg-6 hidden-sm text-right">
                                                <div class="col-6 float-right"> <!-- Adjust width with Bootstrap grid classes -->
                                                    <div class="input-group">
                                                        <input id="searchInputLog" type="text" class="form-control " placeholder="Search log ...">

                                                        <a href="logBook.php" title="Close Log" class="btn btn-outline-danger ml-2"><i class="fa fa-times"></i></a>

                                                    </div>
                                                </div>


                                            </div>

                                        </div>
                                    </div>
                                    <style>
                                        .bg-image {
                                            background-image: url('./img/whatsappWallpaper.png');
                                            /* Replace with your image URL */
                                            background-size: contain;
                                            /* Ensures the image covers the entire div */
                                            background-position: center;
                                            /* Centers the image */
                                            background-repeat: repeat;
                                            /* Prevents the image from repeating */
                                            height: 100vh;
                                            /* Adjust the height as needed */

                                        }
                                    </style>
                                    <div id="chatBody" class=" card-body chat-history bg-image  " style="display: flex; flex-direction: column; height: 80vh;">
                                        <div id="chat-history-content" class="overflow-auto" style="flex:1;">
                                            <ul class="m-b-0 overflow-hidden">
                                                <?php
                                                $logQuery = "SELECT lb.*,sa.fullname FROM customer_logBook lb LEFT JOIN csa_sales_admin sa ON lb.user_id = sa.user_id WHERE lb.contact_id = $contact_id";
                                                $logResult = $conn->query($logQuery);

                                                if ($logResult && $logResult->num_rows > 0) {
                                                    while ($logRow = $logResult->fetch_assoc()) {
                                                        if ($logRow['user_id'] != $user_id) {
                                                ?>
                                                            <li class="clearfix">
                                                                <div class="message-data text-right mr-5">
                                                                    <img src="https://bootdey.com/img/Content/avatar/avatar5.png" alt="Avatar">
                                                                </div>
                                                                <div class="message other-message float-right mr-5">
                                                                    <?php echo htmlspecialchars($logRow['message']); ?>
                                                                    <span class="text-primary font-weight-bolder "><br><span class=" font-weight-bolder text-danger"><?php echo $logRow['fullname']  ?></span>.<?php echo formatTimestamp($logRow['timestamp']); ?></span>
                                                                </div>
                                                            </li>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <br>
                                                            <li class=" ml-2">
                                                                <div class="message-data">
                                                                    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" alt="Avatar">
                                                                </div>
                                                                <div class="message my-message mr-5">
                                                                    <?php echo htmlspecialchars($logRow['message']); ?>
                                                                    <span class="text-primary font-weight-bolder "><br><span class=" font-weight-bolder text-danger"><?php echo $logRow['fullname']  ?></span>.<?php echo formatTimestamp($logRow['timestamp']); ?></span>
                                                                </div>

                                                            </li>
                                                <?php
                                                        }
                                                    }
                                                } else {
                                                    echo "<li class='clearfix'><div class='message my-message text-center'>No log found.</div></li>";
                                                }
                                                $logResult->close();
                                                ?>
                                            </ul>
                                        </div>
                                    </div>



                                    <div id="chatFooter" class="card-footer chat-message clear ">
                                        <form method="POST">
                                            <div class="input-group d-flex align-items-center">
                                                <textarea name="message" type="text" class="form-control" required></textarea>
                                                <button class="box-shadow-none text-decoration-none btn" name="insert_log" type="submit">
                                                    <span class="input-group-text"><i class="fa fa-send text-light"></i></span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>



                                    <script>
                                        document.getElementById('searchInputLog').addEventListener('keyup', function() {
                                            var input = this.value.toLowerCase();
                                            var items = document.querySelectorAll('#chat-history-content li');

                                            items.forEach(function(item) {
                                                if (item.textContent.toLowerCase().includes(input)) {
                                                    item.style.display = '';
                                                } else {
                                                    item.style.display = 'none';
                                                }
                                            });
                                        });
                                    </script>
                                </div>
                            <?php
                            } else {
                                echo "<div class='chat d-none d-sm-block'><div class='chat-header  clearfix d-flex justify-content-center align-items-center' style='height:100vh;'><h6>No Contact Selected.</h6></div></div>";
                            }
                            $contactsResult->close();
                        } else {

                            ?>
                            <div class='chat d-none d-sm-block'>
                                <div id="handBg" class='chat-header  clearfix d-flex justify-content-center flex-column align-items-center' style='height:100vh; '>
                                    <div>
                                        <style>
                                            /* From Uiverse.io by Pradeepsaranbishnoi */
                                            .🤚 {
                                                --skin-color: #E4C560;
                                                --tap-speed: 0.6s;
                                                --tap-stagger: 0.1s;
                                                position: relative;
                                                width: 80px;
                                                height: 60px;
                                                margin-left: 80px;
                                            }

                                            .🤚:before {
                                                content: '';
                                                display: block;
                                                width: 180%;
                                                height: 75%;
                                                position: absolute;
                                                top: 70%;
                                                right: 20%;
                                                background-color: black;
                                                border-radius: 40px 10px;
                                                filter: blur(10px);
                                                opacity: 0.3;
                                            }

                                            .🌴 {
                                                display: block;
                                                width: 100%;
                                                height: 100%;
                                                position: absolute;
                                                top: 0;
                                                left: 0;
                                                background-color: var(--skin-color);
                                                border-radius: 10px 40px;
                                            }

                                            .👍 {
                                                position: absolute;
                                                width: 120%;
                                                height: 38px;
                                                background-color: var(--skin-color);
                                                bottom: -18%;
                                                right: 1%;
                                                transform-origin: calc(100% - 20px) 20px;
                                                transform: rotate(-20deg);
                                                border-radius: 30px 20px 20px 10px;
                                                border-bottom: 2px solid rgba(0, 0, 0, 0.1);
                                                border-left: 2px solid rgba(0, 0, 0, 0.1);
                                            }

                                            .👍:after {
                                                width: 20%;
                                                height: 60%;
                                                content: '';
                                                background-color: rgba(255, 255, 255, 0.3);
                                                position: absolute;
                                                bottom: -8%;
                                                left: 5px;
                                                border-radius: 60% 10% 10% 30%;
                                                border-right: 2px solid rgba(0, 0, 0, 0.05);
                                            }

                                            .👉 {
                                                position: absolute;
                                                width: 80%;
                                                height: 35px;
                                                background-color: var(--skin-color);
                                                bottom: 32%;
                                                right: 64%;
                                                transform-origin: 100% 20px;
                                                animation-duration: calc(var(--tap-speed) * 2);
                                                animation-timing-function: ease-in-out;
                                                animation-iteration-count: infinite;
                                                transform: rotate(10deg);
                                            }

                                            .👉:before {
                                                content: '';
                                                position: absolute;
                                                width: 140%;
                                                height: 30px;
                                                background-color: var(--skin-color);
                                                bottom: 8%;
                                                right: 65%;
                                                transform-origin: calc(100% - 20px) 20px;
                                                transform: rotate(-60deg);
                                                border-radius: 20px;
                                            }

                                            .👉:nth-child(1) {
                                                animation-delay: 0;
                                                filter: brightness(70%);
                                                animation-name: tap-upper-1;
                                            }

                                            .👉:nth-child(2) {
                                                animation-delay: var(--tap-stagger);
                                                filter: brightness(80%);
                                                animation-name: tap-upper-2;
                                            }

                                            .👉:nth-child(3) {
                                                animation-delay: calc(var(--tap-stagger) * 2);
                                                filter: brightness(90%);
                                                animation-name: tap-upper-3;
                                            }

                                            .👉:nth-child(4) {
                                                animation-delay: calc(var(--tap-stagger) * 3);
                                                filter: brightness(100%);
                                                animation-name: tap-upper-4;
                                            }

                                            @keyframes tap-upper-1 {

                                                0%,
                                                50%,
                                                100% {
                                                    transform: rotate(10deg) scale(0.4);
                                                }

                                                40% {
                                                    transform: rotate(50deg) scale(0.4);
                                                }
                                            }

                                            @keyframes tap-upper-2 {

                                                0%,
                                                50%,
                                                100% {
                                                    transform: rotate(10deg) scale(0.6);
                                                }

                                                40% {
                                                    transform: rotate(50deg) scale(0.6);
                                                }
                                            }

                                            @keyframes tap-upper-3 {

                                                0%,
                                                50%,
                                                100% {
                                                    transform: rotate(10deg) scale(0.8);
                                                }

                                                40% {
                                                    transform: rotate(50deg) scale(0.8);
                                                }
                                            }

                                            @keyframes tap-upper-4 {

                                                0%,
                                                50%,
                                                100% {
                                                    transform: rotate(10deg) scale(1);
                                                }

                                                40% {
                                                    transform: rotate(50deg) scale(1);
                                                }
                                            }
                                        </style>
                                        <div class="🤚 mb-5">
                                            <div class="👉"></div>
                                            <div class="👉"></div>
                                            <div class="👉"></div>
                                            <div class="👉"></div>
                                            <div class="🌴"></div>
                                            <div class="👍"></div>
                                        </div>
                                    </div>
                                    <h2 class="mt-5  font-italic font-weight-bolder text-dark" style="letter-spacing: 2px;">Please select a customer to view log</h2>
                                </div>
                            </div>";
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loaderFrame = document.getElementById('loaderFrame');
        const mainFrame = document.getElementById('mainFrame');
        const navbar = document.getElementById('navbar');
        const urlParams = new URLSearchParams(window.location.search);
        const logBookStatus = urlParams.get('status');

        if (logBookStatus === 'open') {
            // Show the loader initially
            loaderFrame.classList.add("d-block");
            loaderFrame.classList.remove("d-none");
            navbar.classList.add("d-none");

            mainFrame.classList.add("d-none");
            mainFrame.classList.remove("d-block");

            // After 3 seconds, hide the loader and show the main content
            setTimeout(() => {
                loaderFrame.classList.add("d-none");
                loaderFrame.classList.remove("d-block");

                mainFrame.classList.add("d-block");
                mainFrame.classList.remove("d-none");
                navbar.classList.remove("d-none");
                // Clear the URL parameters without reloading the page
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }, 3000); // 3000 milliseconds = 3 seconds
        }

    });
</script>
<script>
    document.getElementById('people-list-toggle').addEventListener('click', function() {
        document.getElementById('plist').classList.toggle('d-none');
        document.getElementById('chat-history-content').classList.toggle('d-none');
        document.getElementById('chat-header').classList.toggle('d-none');
    });
</script>


<?php

if (isset($_POST['insert_log'])) {
    $contact_id = $_GET['contact_id'];
    $message = $_POST['message'];
    $sql = "INSERT INTO customer_logBook (contact_id,message,user_id) VALUES(?,?,?) ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $contact_id, $message, $user_id);
    $stmt->execute();
    $stmt->close();
    header('location:' . $_SERVER['HTTP_REFERER']);
}

?>

<script>
    // Function to scroll to the bottom of the chat
    function scrollToBottom() {
        var chatContent = document.getElementById('chat-history-content');
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    // Scroll to the bottom on page load
    window.onload = function() {
        scrollToBottom();
    };
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeStateBtn = document.getElementById('themeState');
        const chatFooter = document.getElementById('chatFooter');
        const chatBody = document.getElementById('chatBody');
        const handBg = document.getElementById('handBg');
        // Check the stored theme preference on page load
        const storedTheme = sessionStorage.getItem('theme') ? sessionStorage.getItem('theme') : 'light-mode';

        if (storedTheme === 'light-mode') {
            chatFooter.style.backgroundColor = 'white'; // light color
            chatBody.style.backgroundColor = '#EFEAE2'; // light color
            handBg.style.backgroundColor = 'white'; // light color

        } else {
            chatFooter.style.backgroundColor = '#191B25'; // Dark color
            chatBody.style.backgroundColor = ' #0c2d1c';
            handBg.style.backgroundColor = '#191B25 ';


        }


    });
</script>