<div id="authloginpass_login_zone">
{if $failed}
<p>{@authloginpass~auth.message.failedToLogin@}</p>
{/if}

{if ! $isAuthenticated}

    <form action="{formurl 'authloginpass~sign:checkCredentials'}" method="post" id="loginForm">
        <div class="form-group has-feedback">
            <input name="login" id="login" class="form-control"  value="{$login|eschtml}" placeholder="{@authloginpass~auth.form.login@}">
            <span class="glyphicon form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password"  name="password" id="password" class="form-control" placeholder="{@authloginpass~auth.form.password@}">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <a href="{jurl 'authloginpass~password_reset:index'}">{@authloginpass~auth.form.password.forget@}</a>
                {formurlparam 'authloginpass~sign:checkCredentials'}
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <button type="submit" class="btn btn-primary btn-block btn-flat">{@authloginpass~auth.form.buttons.login@}</button>
            </div>
            <!-- /.col -->
        </div>
   </form>
{else}
    <p>{$user->getName()} | <a href="{jurl 'authcore~sign:out'}" >{@authcore~auth.link.logout@}</a></p>
{/if}
</div>
