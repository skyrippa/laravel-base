<html lang="pt-BR">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <title>@yield('template_title')</title>
    <meta name="description" content="Reset Password Email Template.">
    <style type="text/css">
        a:hover {
            text-decoration: underline !important;
        }

        .button {
            background: #0e2b5e;
            text-decoration: none !important;
            color: #FFF !important;
            font-weight: 500;
            margin-top: 35px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-size: 14px;
            padding: 10px 24px;
            display: inline-block;
            border-radius: 50px;
        }

        .text {
            color: #455056;
            font-size: 15px;
            line-height: 24px;
            margin: 0;
        }

        .table {
            background-color: #f2f3f8;
            max-width: 670px;
            margin: 0 auto;
        }

        .table-square {
            max-width: 670px;
            background: #fff;
            border-radius: 3px;
            text-align: center;
            -webkit-box-shadow: 0 6px 18px 0 rgba(0, 0, 0, .06);
            -moz-box-shadow: 0 6px 18px 0 rgba(0, 0, 0, .06);
            box-shadow: 0 6px 18px 0 rgba(0, 0, 0, .06);
        }

        .text-title {
            color: #1e1e2d;
            font-weight: 500;
            margin: 0;
            font-size: 32px;
            font-family: 'Rubik', sans-serif;
        }

        .divisor {
            display: inline-block;
            vertical-align: middle;
            margin: 29px 0 26px;
            border-bottom: 1px solid #cecece;
            width: 100px;
        }

        .footer-text {
            font-size: 14px;
            color: rgba(69, 80, 86, 0.7411764705882353);
            line-height: 18px;
            margin: 0 0 0;
        }

        .token {
            margin-bottom: 0;
            margin-top: 20px;
            font-size: 40px;
        }

        .store-button {
            text-decoration: none;
        }

        .store-button-img {
            height: 53px;
        }

        .store-button:hover {
            text-decoration: none !important;
        }
    </style>
</head>
<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
<table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
       style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
    <tr>
        <td>
            <table class="table" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="height:80px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <h1>System.io</h1>
                    </td>
                </tr>
                <tr>
                    <td style="height:20px;">&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                               class="table-square">
                            <tr>
                                <td style="height:40px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="padding:35px;">
                                    <h1 class="text-title">
                                        @yield('title')
                                    </h1>
                                    <span class="divisor"></span>
                                    <p class="text">
                                        @yield('text')
                                    </p>

                                    @if (View::hasSection('token'))
                                        <p class="token">
                                            @yield('token')
                                        </p>
                                    @endif

                                    @if (View::hasSection('button_text'))
                                        <a target="_blank" class="button" href="@yield('button_url')">
                                            @yield('button_text')
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                <tr>
                    <td style="height:20px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <p class="footer-text">
                            &copy; <strong>System.io</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="height:80px;">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>
