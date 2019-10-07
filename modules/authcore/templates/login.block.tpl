<div id="auth-login-zone">
{ifuserauthenticated}
    <p>{@authcore~auth.error.already.authenticated@}</p>
{else}
    {if $htmlForms}
    {foreach $htmlForms as $htmlform}
        {$htmlform}
    {/foreach}
    {else}
        <p>{@authcore~auth.error.no.login.form@}</p>
    {/if}
{/ifuserauthenticated}
</div>
