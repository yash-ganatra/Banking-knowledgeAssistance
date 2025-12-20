<tr>
    <td>
        <span id="entity_details">
                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                        <tbody>
                            <tr>
                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                        {{$label_entity_details}}
                                </td>
                            </tr>
                            <tr>
                                <td height="15"></td>
                            </tr>
                                <tr>
                                    <td style="padding-left: 10px!important;">
                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                {{$label_entity_name}}
                                            </td>
                                            
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{strtoupper($entityDetails['entity_name'])}}
                                                </span>
                                            </td>
                                            
                                        </tr>
                                            <td height="8"></td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                {{$label_proof_of_entity_address}}
                                            </td>
                                            
                                            <td style="line-height: 30px;" width="20%">
                                                Proof (Reference Number)
                                            </td>
                                        </tr>
                                            <tr>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; display: inline-block; color:#0070C0">
                                                        {{strtoupper(substr($label_proof_of_entity_address_name,0,30))}}
                                                    </span>
                                                </td>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%;  display: inline-block; color:#0070C0">
                                                        {{strtoupper($entityDetails['entity_add_proof_card_number'])}}
                                                    </span>
                                                </td>
                                                
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    {{$label_entity_address}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%; */display: inline-block; color:#0070C0">
                                                        {{strtoupper($entityDetails['entity_address_line1'])}}</br>
                                                        {{strtoupper($entityDetails['entity_address_line2'])}}</br>
                                                        {{strtoupper($entityDetails['name'])}}</br>
                                                        {{strtoupper($entityDetails['entity_pincode'])}}</br>
                                                        {{strtoupper($entityDetails['entity_state'])}}</br>
                                                        {{strtoupper($entityDetails['entity_city'])}}</br>
                                                        {{strtoupper($entityDetails['entity_landmark'])}}
                                                    </span>
                                                </td>
                                            </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    
                        <!-- <tr>
                            <td style="padding-left: 10px!important;">
                                <table style="padding-left: 10px; padding-right: 10px;" width="60%">
                                    <tbody>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Existing Customer:
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;margin-bottom:10px;" width="40%">
                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:100%; height:100%; display: inline-block; color:#0070C0">
                                                    {{strtoupper($customerOvdDetails['customer_full_name'])}} [CUSTID: {{$customerOvdDetails['customer_id']}}]
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
-->
                    <tr>
                        <td height="20"></td>
                    </tr>
                </tbody>
            </table>
        </span>
    </td>
</tr>