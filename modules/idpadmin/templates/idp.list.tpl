
<h2>{@idpadmin~default.idp.list.page.title@}</h2>
<div class="card">
<div class="card-body">

{formcontrols $form}

<table class="table table-bordered table-striped">
    <thead>
    <tr>
    <th>{@idpadmin~default.table.th.name@}</th>
    <th>{@idpadmin~default.table.th.status@}</th>
    </tr>
    </thead>
    <tbody>

    {foreach $idps as $idp}
        {assign $ctlName = 'chck_'.$idp[0]}
        <tr>
            <td class="col-sm-4">
                {$idp[0]}
            </td>
            <td class="col-sm-4">
                {ctrl_value $ctlName}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

{/formcontrols}
{ifacl2 'idpadmin.edit'}
<a href='{jurl 'idpadmin~default:prepareEdit'}' class='btn btn-primary' >{@jelix~ui.buttons.update@}</a>
{/ifacl2}
</div>
</div>
