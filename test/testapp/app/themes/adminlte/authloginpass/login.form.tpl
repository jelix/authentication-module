<div id="authloginpass_login_zone">
{if $failed}
<p>{@authloginpass~auth.message.failedToLogin@}</p>
{/if}

{if ! $isAuthenticated}

    <form action="{formurl 'authloginpass~sign:checkCredentials'}" method="post" id="loginForm">
        <div class="form-group has-feedback">
            <label for="login">{@authloginpass~auth.form.login@}</label>
            <input name="login" id="login" class="form-control"  value="{$login|eschtml}" placeholder="">
            <span class="glyphicon form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <label for="password">{@authloginpass~auth.form.password@}</label>
            <input type="password"  name="password" id="password" class="form-control" placeholder="">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-actions">
            {formurlparam 'authloginpass~sign:checkCredentials'}
                <button type="submit" class="btn btn-primary btn-block btn-flat">{@authloginpass~auth.form.buttons.login@}</button>

                <p><a href="{jurl 'authloginpass~password_reset:index'}">{@authloginpass~auth.form.password.forget@}</a></p>
        </div>
   </form>
{else}
    <p>{$user->getName()} | <a href="{jurl 'authcore~sign:out'}" >{@authcore~auth.link.logout@}</a></p>
{/if}
</div>
