<div>
    <h1>{@auth.form.title@}</h1>

    {@auth.admin.waiting.text.html@}

    <p><a href="{jurl 'jauthdb_admin~default:view', array('j_user_login'=>$login)}">{@auth.admin.form.back.to.account@} {$login}</a></p>
</div>