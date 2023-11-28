<div class="loginpass-error">
    <h1>{@authloginpass~auth.access.title@}</h1>
    {if $error == 'no_access_wronguser'}
        <p class="error">{@authloginpass~auth.access.forbidden.wrong.user@}</p>
        {if $canViewProfile}
        <p><a href="{jurl 'account~profile:index'}">{@account~account.back.to.profile@}</a></p>
        {/if}
    {elseif $error == 'no_access_auth'}
        <p class="error">{@authloginpass~auth.access.forbidden.authenticated@}</p>
        <p><a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.back.to.login@}</a></p>
    {elseif $error == 'no_access_badstatus'}
        <p class="error">{@authloginpass~auth.access.forbidden.badstatus@}</p>
        {if $login}
            <p><a href="{jurl 'account~profile:index'}">{@account~account.back.to.profile@}</a></p>
        {else}
            <p><a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.back.to.login@}</a></p>
        {/if}
    {elseif $error == 'not_available'}
        <p class="error">{@authloginpass~auth.access.not.available@}</p>
        {if $login}
        <p><a href="{jurl 'account~profile:index'}">{@account~account.back.to.profile@}</a></p>
        {else}
        <p><a href="{jurl 'authcore~sign:in'}">{@authloginpass~auth.back.to.login@}</a></p>
        {/if}
    {else}
        <p class="error">Error {$error}</p>
    {/if}
</div>
