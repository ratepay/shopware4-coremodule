
<div id="ratepay_ratenrechner_div">
            <div id="ratepay_ratenrechner_inneres_div">
                <link type="text/css" rel="stylesheet" href="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePay/Views/frontend/Ratenrechner/css/style.css' fullPath}"/>
                <script type="text/javascript">
                    pi_ratepay_rate_calc_path = "{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePay/Views/frontend/Ratenrechner/' fullPath}";
                    pi_ratepay_rate_ajax_path = "{url controller="PigmbhRatepay" action=""}";
                </script>
                <script type="text/javascript" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePay/Views/frontend/Ratenrechner/js/mouseaction.js' fullPath}"></script>
                <script type="text/javascript" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePay/Views/frontend/Ratenrechner/js/layout.js' fullPath}"></script>
                <script type="text/javascript" src="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePay/Views/frontend/Ratenrechner/js/ajax.js' fullPath}"></script>
                <div id="pirpmain-cont" name="pirpratenrechnerContent"></div>
                <script type="text/javascript">
                    if(document.getElementById('pirpmain-cont')) {
                    piLoadrateCalculator();
                }
                </script>
            </div>
        </div>