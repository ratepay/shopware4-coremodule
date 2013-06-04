<div class='error' style='display: none;'>
    <div id='ratepay_error'></div>
</div>
<script language='javascript'>
    $(document).ready(function() {
    {if $ratepayValidateisAgeValid != 'true'}
        $("#ratepay_error").append({s namespace=RatePAY name=invalidAge}'Das angegebene Alter ist unter 18!'{/s});
        $("#ratepay_error").parent().show();
    {/if}
    });
</script>