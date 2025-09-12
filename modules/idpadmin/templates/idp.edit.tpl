<h2>{@idpadmin~default.idp.edit.page.title@}</h2>

<div class="card">
<div class="card-body">
{form $form, 'idpadmin~default:save', [], 'adminlte'}

{formcontrols}

<table class="table table-bordered table-striped">
    <thead>
    <tr><th>{@idpadmin~default.table.th.name@}</th>
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
                {ctrl_control  $ctlName}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

{/formcontrols}

<div class="form-group row">
    <div class="col-sm-10 offset-sm-2 ">{formsubmit}</div>
</div>
{/form}
</div>
</div>
