<div>
    <h1>{@authloginpass~password.form.title@}</h1>
    {@authloginpass~password.admin.form.reset.html@}
    <form method="post" action="{jurl 'authloginpass~password_reset_admin:send'}">
        <p>
            <input type="hidden" name="pass_login" value="{$login}">
            <button>{@authloginpass~password.admin.form.email.button@} {$login}</button></p>
    </form>

    <p><a href="{jurl 'jauthdb_admin~default:view', array('j_user_login'=>$login)}">{@authloginpass~password.admin.form.back.to.account@} {$login}</a></p>
</div>