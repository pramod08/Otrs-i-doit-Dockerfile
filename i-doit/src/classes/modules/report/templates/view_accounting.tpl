<div class="p10">
    <table class="contentTable">
        <tr>
            <td></td>
            <td class="bold">
                <p class="mb5">Bitte wählen Sie einen Zeitraum aus, für den der Report dargestellt werden soll</p>
            </td>
        </tr>
        <tr>
            <td class="key"><label for="C__CALENDAR_FROM">Zeitraum von</label></td>
            <td class="value">[{isys type="f_popup" name="C__CALENDAR_FROM" p_strPopupType="calendar" p_strValue=$from p_bTime="0" p_bEditMode="1"}]</td>
        </tr>
        <tr>
            <td class="key"><label for="C__CALENDAR_TO">Zeitraum bis</label></td>
            <td class="value">[{isys type="f_popup" name="C__CALENDAR_TO" p_strPopupType="calendar" p_strValue=$to p_bTime="0" p_bEditMode="1"}]</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <a href="#" class="navbar_item" style="margin-left:20px;" onclick="$('isys_form').submit();">Daten laden!</a>
            </td>
        </tr>
    </table>

    <h3 class="mt10 mb10 gradient text-shadow p5">Report</h3>

    <table width="90%" class="listing">
        <thead>
        <tr style="padding-bottom:5px;">
            <th class="pl5">
                [{isys type='lang' ident='Objekt ID'}]
            </th>
            <th class="pl5">
                [{isys type='lang' ident='Objektlink'}]
            </th>
            <th class="pl5">
                [{isys type='lang' ident='Änderungsdatum'}]
            </th>
            <th class="pl5">
                [{isys type='lang' ident='Benutzer'}]
            </th>
            <th class="pl5">
                [{isys type='lang' ident='Wert vorher'}]
            </th>
            <th class="pl5">
                [{isys type='lang' ident='Wert nachher'}]
            </th>
        </tr>
        </thead>
        <tbody id="view_object_list">
        [{* The content will be added via Javascript. *}]
        </tbody>
    </table>
</div>


<script type="text/javascript">
    var data_json = [{$data}];

    data_json.each(function(e) {
        var tr = new Element('tr')
                .insert(new Element('td').update(e[0]))
                .insert(new Element('td').update(e[1]))
                .insert(new Element('td').update(e[2]))
                .insert(new Element('td').update(e[3]))
                .insert(new Element('td').update(e[4]))
                .insert(new Element('td').update(e[5]));

        $('view_object_list').insert(tr);
    }.bind(this));
</script>