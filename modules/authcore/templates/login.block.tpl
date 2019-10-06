<div id="auth-login-zone">
{ifuserauthenticated}
    {if $htmlForms}
    {foreach $htmlForms as $htmlform}
        {$htmlform}
    {/foreach}
    {else}
        <p>{@authcore~auth.error.no.login.form@}</p>
    {/if}
{else}
    <p>{@authcore~auth.error.already.authenticated@}</p>
{/ifuserauthenticated}
</div>