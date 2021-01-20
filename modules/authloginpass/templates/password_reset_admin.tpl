<div>
    <h1>{@auth.form.title@}</h1>
    {@auth.admin.form.reset.html@}
    <form method="post" action="{jurl 'authloginpass~password_reset_admin:send'}">
        <p>
            <input type="hidden" name="pass_login" value="{$login}">
            <button>{@auth.admin.form.email.button@} {$login}</button></p>
    </form>

    <p><a href="{jurl 'jauthdb_admin~default:view', array('j_user_login'=>$login)}">{@auth.admin.form.back.to.account@} {$login}</a></p>
</div>