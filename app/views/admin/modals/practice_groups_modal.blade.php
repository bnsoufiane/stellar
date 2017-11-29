<div class="form-group list-component" id="items_pool">
    {{ Form::label('practice_groups', 'Assigned Practice Groups', array('class' => 'control-label')) }}<br/><br/>

    <ul>

        @foreach($practice_groups as $practice_group)
            <li><span>{{$practice_group->name}}</span>&nbsp;
                <a class="remove_pool_item remove-option"><i class="icon_minus_alt"></i></a>
                <input type="hidden" name="practice_groups_id[]" value="{{$practice_group->id}}"/>
            </li>
        @endforeach
    </ul>

    <a class="add-new" data-toggle="modal" data-target="#items_modal">Add Practice Group</a>
</div>


<!-- Modal -->
<div id="items_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Choose Practice Groups</h4>
            </div>
            @if(count($all_practice_groups)>0)
                <div class="modal-body">
                    <table class="users-table">
                        <thead class="no-border">
                        <tr>
                            <th style="width: 5%;">&nbsp;</th>
                            <th style="width: 95%;">Practice Groups</th>
                        </tr>
                        </thead>
                        <tbody class="no-border-x">
                        <tbody class="no-border-x">
                        @foreach($all_practice_groups as $practice_group_item)
                            <tr>
                                <td><input type="checkbox" value="{{$practice_group_item->id}}"></td>
                                <td>{{$practice_group_item->name}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    No practice_groups are available.
                </div>
            @endif
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="choose_items" items-obj="practice_groups_id">Choose</button>
            </div>
        </div>
    </div>
</div>



