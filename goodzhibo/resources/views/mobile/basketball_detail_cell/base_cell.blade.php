 <div id="Event" class="childNode" style="display: ;">
        <div class="score default">
            <div class="title">比分统计<button class="close"></button></div>
            <table>
                <thead>
                <tr>
                    <th>球队</th>
                    <th>1st</th>
                    <th>2nd</th>
                    <th>3rd</th>
                    <th>4th</th>
                    @if((isset($match['h_ot']) && strlen($match['h_ot']) > 0)||(isset($match['a_ot']) && strlen($match['a_ot']) > 0))
                        <th>OT</th>
                    @endif
                    <th>总分</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><img src="{{strlen($match['hicon']) > 0 ? $match['hicon'] : '/img/icon_teamDefault.png'}}"></td>
                    <td
                            @if($match['status'] == 1)
                            class="now"
                            @endif
                    >{{$match['hscore_1st'] or '/'}}</td>
                    <td
                            @if($match['status'] == 2)
                            class="now"
                            @endif
                    >{{$match['hscore_2nd'] or '/'}}</td>
                    <td
                            @if($match['status'] == 3)
                            class="now"
                            @endif
                    >{{$match['hscore_3rd'] or '/'}}</td>
                    <td
                            @if($match['status'] == 4)
                            class="now"
                            @endif
                    >{{$match['hscore_4th'] or '/'}}</td>
                    @if((isset($match['h_ot']) && strlen($match['h_ot']) > 0)||(isset($match['a_ot']) && strlen($match['a_ot']) > 0))
                        <td>{{$match['h_ot'] or '/'}}</td>
                    @endif
                    <td>{{$match['hscore'] or '/'}}</td>
                </tr>
                <tr>
                    <td><img src="{{strlen($match['aicon']) > 0 ? $match['aicon'] : '/img/icon_teamDefault.png'}}"></td>
                    <td
                            @if($match['status'] == 1)
                            class="now"
                            @endif
                    >{{$match['ascore_1st'] or '/'}}</td>
                    <td
                            @if($match['status'] == 2)
                            class="now"
                            @endif
                    >{{$match['ascore_2nd'] or '/'}}</td>
                    <td
                            @if($match['status'] == 3)
                            class="now"
                            @endif
                    >{{$match['ascore_3rd'] or '/'}}</td>
                    <td
                            @if($match['status'] == 4)
                            class="now"
                            @endif
                    >{{$match['ascore_4th'] or '/'}}</td>
                    @if((isset($match['h_ot']) && strlen($match['h_ot']) > 0)||(isset($match['a_ot']) && strlen($match['a_ot']) > 0))
                        <td>{{$match['a_ot'] or '/'}}</td>
                    @endif
                    <td>{{$match['ascore'] or '/'}}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>