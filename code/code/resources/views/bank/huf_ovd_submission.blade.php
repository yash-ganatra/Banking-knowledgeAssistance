<tr>
    <td>
        <span id="ovd_details">
            <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                <tbody>
                    <tr>
                        <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;"
                            height="30">
                            @if ($is_huf_display)
                                PERSONAL DETAILS: {{ $i == 1 ? 'KARTA/MANAGER' : 'HUF' }}
                            @else
                                PERSONAL DETAILS: {{ $i == 1 ? 'PRIMARY APPLICANT' : 'APPLICANT ' . $i }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td height="15"></td>
                    </tr>
                    @if ($customer_type != 'ETB')
                        <tr>
                            <td style="padding-left: 10px!important;">
                                <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                {{ $huf_display ? ' Name' : ' Name (Name as per OVD)' }}
                                            </td>
                                            @if (!$huf_display)
                                                <td style="line-height: 30px;" width="20%">
                                                    Name on Card (Short Name)
                                                </td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{ strtoupper($customerOvdDetails['title']) . ' ' . $customerOvdDetails['first_name'] . ' ' . $customerOvdDetails['middle_name'] . ' ' . $customerOvdDetails['last_name'] }}
                                                </span>
                                            </td>
                                            @if (!$huf_display)
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        {{ strtoupper($customerOvdDetails['short_name']) }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                        <tr>
                                            @if ($customerOvdDetails['pf_type'] == 'pancard')
                                                <td style="line-height: 30px;" width="20%">
                                                    PAN Number
                                                </td>
                                            @endif
                                            <td style="line-height: 30px;" width="20%">
                                                {{ $huf_display ? 'DOF (Date of Formation)' : 'Date of Birth' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            @if ($customerOvdDetails['pf_type'] == 'pancard')
                                                <td style="line-height: 30px;" width="30%">

                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0;">
                                                        @if ($is_review == 1 || $i_ao_trac)
                                                            <span class="maskingfield">************</span>
                                                        @endif

                                                        <span
                                                            {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                            class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }}">
                                                            <label
                                                                class="{{ $is_review == 1 || $i_ao_trac ? '' : 'enc_label' }}">
                                                                {{ $customerOvdDetails['pancard_no'] }}
                                                            </label>
                                                        </span>
                                                    </span>

                                                </td>
                                            @endif
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{ strtoupper(\Carbon\Carbon::parse($customerOvdDetails['dob'])->format('d M Y')) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                        @if (!$huf_display)
                                            {{--  Start non display in huf  --}}
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    {{ $customerOvdDetails['proof_of_identity'] }} Number
                                                </td>
                                                @if ($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                    <td style="line-height: 25px;" width="20%">
                                                        {{ $customerOvdDetails['proof_of_identity'] }} Expire
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                    <td style="line-height: 25px;" width="20%">
                                                        {{ $customerOvdDetails['proof_of_identity'] }} Expire
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Aadhaar')
                                                    <td style="line-height: 25px;" width="20%">
                                                        Link Aadhar to Account
                                                    </td>
                                                @endif
                                            </tr>



                                            <tr>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        @if ($customerOvdDetails['proof_of_identity'] == 'Aadhaar Photocopy')
                                                            XXXX-XXXX{{ substr($customerOvdDetails['id_proof_card_number'], 9, 11) }}
                                                        @elseif(in_array($customerOvdDetails['proof_of_identity'], $enc_fields))
                                                            @if ($is_review == 1 || $i_ao_trac)
                                                                <span class="maskingfield">************</span>
                                                            @endif
                                                            <span
                                                                {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                                class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }} enc_label">
                                                                {{ $customerOvdDetails['id_proof_card_number'] }}
                                                            </span>
                                                        @else
                                                            <span>
                                                                {{ $customerOvdDetails['id_proof_card_number'] }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </td>

                                                @if ($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            {{ strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y')) }}

                                                        </span>
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            {{ strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y')) }}

                                                        </span>
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Aadhaar')
                                                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            @if ($customerOvdDetails['aadhar_link'] == 1)
                                                                YES
                                                            @else
                                                                NO
                                                            @endif
                                                        </span>
                                                    </td>
                                                @endif
                                            </tr>
                                            <tr>
                                                <td height="8"></td>
                                            </tr>
                                            <tr>

                                                @if ($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                    <td style="line-height: 25px;" width="20%">
                                                        {{ $customerOvdDetails['proof_of_identity'] }} Issue
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                    <td style="line-height: 25px;" width="20%">
                                                        {{ $customerOvdDetails['proof_of_identity'] }} Issue
                                                    </td>
                                                @endif
                                            </tr>

                                            <tr>
                                                @if ($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            {{ strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y')) }}

                                                        </span>
                                                    </td>
                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            {{ strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y')) }}

                                                        </span>
                                                    </td>
                                                @endif
                                            </tr>

                                            <tr>
                                                <td height="8"></td>
                                            </tr>

                                            @if (isset($customerOvdDetails['ekyc_photo']) && $customerOvdDetails['ekyc_photo'] != '')
                                                <tr>
                                                    <td style="line-height: 25px;" width="20%">
                                                        E-KYC Photo
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="line-height: 50px;width:16%;padding-bottom: 3em">
                                                        <div class="{{ $image_mask_blur }}" {{ $def_blur_image }}>
                                                            <img width="160px" alt=""
                                                                src="{{ 'data: image/jpeg;base64,' . $customerOvdDetails['ekyc_photo'] }}" />
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td height="8"></td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    @if ($customerOvdDetails['father_spouse'] == 1)
                                                        Father Name
                                                    @else
                                                        Spouse Name
                                                    @endif
                                                </td>
                                                <td style="line-height: 25px;" width="20%">
                                                    Mother's Full Name
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        {{ strtoupper($customerOvdDetails['father_name']) }}
                                                    </span>
                                                </td>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        {{ strtoupper($customerOvdDetails['mother_full_name']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="8"></td>
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    Mother’s Maiden Name
                                                </td>
                                                <td style="line-height: 30px;" width="20%">
                                                    Gender
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        {{ strtoupper($customerOvdDetails['mothers_maiden_name']) }}
                                                    </span>
                                                </td>
                                                <td style="line-height: 30px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                        {{ strtoupper(config('constants.GENDER.' . $customerOvdDetails['gender'])) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif {{--  end non display in huf  --}}
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Martial Status
                                            </td>
                                            <td style="line-height: 30px;" width="20%">
                                                Residential Status
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{ strtoupper($customerOvdDetails['marital_status']) }}
                                                </span>
                                            </td>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{ strtoupper($customerOvdDetails['residential_status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="8"></td>
                                        </tr>

                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Mobile Number
                                            </td>
                                            <td style="line-height: 30px;" width="20%">
                                                Email
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    @if ($is_review == 1 || $i_ao_trac)
                                                        <span class="maskingfield">*********</span>
                                                    @endif
                                                    <span {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                        class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }} enc_label">
                                                        {{ $customerOvdDetails['mobile_number'] }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    @if ($is_review == 1 || $i_ao_trac)
                                                        <span class="maskingfield">************</span>
                                                    @endif
                                                    <span {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                        class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }} enc_label">
                                                        {{ $customerOvdDetails['email'] }}
                                                    </span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                       
                                        @if($customerOvdDetails['address_per_flag'] == '' ||$customerOvdDetails['address_per_flag'] == '0')
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Proof Of Permanent Address Type
                                            </td>
                                            <td style="line-height: 30px; white-space:wrap;" width="20%">
                                                    {{ $customerOvdDetails['proof_of_address'] . ' Number' }}

                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0;white-space:wrap;">
                                                    {{ strtoupper($customerOvdDetails['proof_of_address']) }}
                                                </span>
                                            </td>
                                            <td style="line-height: 30px;" width="20%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0;white-space:wrap;">
                                                    @if ($customerOvdDetails['proof_of_address'] == 'Aadhaar Photocopy')
                                                            XXXX-XXXX{{ substr($customerOvdDetails['add_proof_card_number'], 9, 11) }}
                                                    @elseif (in_array($customerOvdDetails['proof_of_address'], $enc_fields))
                                                        @if ($is_review == 1 || $i_ao_trac)
                                                            <span class="maskingfield">************</span>
                                                        @endif
                                                        <span
                                                            {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                            class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }}">
                                                            <label
                                                                class="{{ $is_review == 1 || $i_ao_trac ? '' : 'enc_label' }}">
                                                                {{ $customerOvdDetails['add_proof_card_number'] }}
                                                            </label>
                                                        </span>
                                                    @else
                                                        <span>
                                                            <label class="enc_label">
                                                                {{ $customerOvdDetails['add_proof_card_number'] }}
                                                            </label>
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                        @if (
                                            !empty($customerOvdDetails['passport_driving_expire_permanent']) &&
                                                in_array($customerOvdDetails['proof_of_address'], ['Passport', 'Driving Licence']))
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    {{ strtoupper($customerOvdDetails['proof_of_address']) }} Expiry
                                                    Date</td>
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                        {{ date('d-M-Y', strtotime($customerOvdDetails['passport_driving_expire_permanent'])) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif

                                        @if (
                                            !empty($customerOvdDetails['add_psprt_dri_issue']) &&
                                                in_array($customerOvdDetails['proof_of_address'], ['Passport', 'Driving Licence']))
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    {{ strtoupper($customerOvdDetails['proof_of_address']) }} Issue
                                                    Date</td>
                                            </tr>
                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                        {{ date('d-M-Y', strtotime($customerOvdDetails['add_psprt_dri_issue'])) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                 @endif
                                        <tr> 
                                            @if($accountDetails['constitution'] == 'NON_IND_HUF' && $i ==2 && $customerOvdDetails['address_per_flag'] ==1)
                                                <td style="line-height: 30px;" width="20%">
                                                    Same as Karta Address (Registered)
                                                </td>
                                                @else

                                                <td style="line-height: 30px;" width="20%">
                                                    {{ $huf_display ? 'Registered Address' : 'Address (as per OVD)' }}
                                                </td>
                                           @endif

                                          
                                        </tr>

                                        <tr>
                                            <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                    {{ strtoupper($customerOvdDetails['per_address_line1']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_address_line2']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_country']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_pincode']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_state']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_city']) }}</br>
                                                    {{ strtoupper($customerOvdDetails['per_landmark']) }}
                                                </span>
                                            </td>
                                        </tr>

                                        {{-- huf current display --}}
                                        @if ($is_huf_display)

                                            @if ($customerOvdDetails['proof_of_current_address'] != '')
                                                <tr>
                                                    <td style="line-height: 30px;" width="20%">
                                                        Proof of Communication Address
                                                    </td>
                                                    <td style="line-height: 30px; white-space:wrap;" width="20%">
                                                        Number</td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height: 30px;" width="20%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0; white-space:wrap;">
                                                            {{ strtoupper($customerOvdDetails['proof_of_current_address']) }}
                                                        </span>
                                                    </td>
                                                    <td style="line-height: 30px;" width="20%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0; white-space:wrap;">
                                                            @if (in_array($customerOvdDetails['proof_of_current_address'], $enc_fields))
                                                                @if ($is_review == 1 || $i_ao_trac)
                                                                    <span class="maskingfield">************</span>
                                                                @endif
                                                                <span
                                                                    {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                                    class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }}">
                                                                    <label
                                                                        class="{{ $is_review == 1 || $i_ao_trac ? '' : 'enc_label' }}">
                                                                        {{ $customerOvdDetails['current_add_proof_card_number'] }}
                                                                    </label>
                                                                </span>
                                                            @else
                                                                <span>
                                                                    <label class="enc_label">
                                                                        {{ $customerOvdDetails['current_add_proof_card_number'] }}
                                                                    </label>
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </td>
                                                </tr>
                                                @if (
                                                    !empty($customerOvdDetails['passport_driving_expire_communication']) &&
                                                        in_array($customerOvdDetails['proof_of_current_address'], ['Passport', 'Driving Licence']))
                                                    <tr>
                                                        <td style="line-height: 30px;" width="20%">
                                                            {{ strtoupper($customerOvdDetails['proof_of_current_address']) }}
                                                            Expiry Date</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="line-height: 30px;" width="20%">
                                                            <span
                                                                style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0; white-space:wrap;">
                                                                {{ date('d-M-Y', strtotime($customerOvdDetails['passport_driving_expire_communication'])) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif

                                            @php
                                            if($accountDetails['constitution'] == 'NON_IND_HUF' && $i ==2 &&$customerOvdDetails['address_per_flag'] ==0 && $customerOvdDetails['address_flag'] ==0){
                                                $msg = "Same as Registered Address (Communication Address)";
                                            }
                                            elseif ($accountDetails['constitution'] == 'NON_IND_HUF' && $i ==2 &&$customerOvdDetails['address_per_flag'] ==1 && $customerOvdDetails['address_flag'] ==1){
                                                $msg =  "Same as Karta Communication Address";
                                            }else{
                                                $msg= "Communication Address";
                                            }
                                           @endphp

                                            <tr>
                                                <td style="line-height: 30px;" width="20%">
                                                    {{$msg}}
                                                </td>
                                                @if ($huf_display)
                                                    <td style="line-height: 30px;" width="20%">
                                                        Relationship Between HUF & Signatory
                                                    </td>
                                                @endif
                                            </tr>
                                            <tr>
                                                <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                                                    <span
                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%; */display: inline-block; color:#0070C0">
                                                        {{ strtoupper($customerOvdDetails['current_address_line1']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_address_line2']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_country']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_pincode']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_state']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_city']) }}</br>
                                                        {{ strtoupper($customerOvdDetails['current_landmark']) }}
                                                    </span>
                                                </td>
                                                @if ($huf_display)
                                                    <td style="line-height: 30px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%;margin-bottom: 21%; display: inline-block; color:#0070C0">
                                                            {{ strtoupper($customerOvdDetails['huf_signatory_relation']) }}
                                                        </span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
                                        {{-- huf current display end --}}

                                        @if ($huf_display)
                                            @foreach ($huf_cop_row as $key => $co)
                                                @php
                                                    $co = (array) $co;
                                                @endphp
                                                <tr>
                                                    <td style="line-height: 30px;" colspan="2" width="">

                                                        <div class="row m-0">
                                                            <div class="col-3 flex-column">
                                                                <label> Coparcenor Name -{{ $key + 1 }}</label>
                                                            </div>
                                                            <div class="col-3">
                                                                <label>Coparcenor Type -{{ $key + 1 }}</label>

                                                            </div>
                                                            <div class="col-3">
                                                                <label>Coparcenor Relation -{{ $key + 1 }}</label>

                                                            </div>
                                                            <div class="col-3">
                                                                <label>Coparcenor DOB -{{ $key + 1 }}</label>

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height: 30px;" colspan="2" width="">

                                                        <div class="row m-0">
                                                            <div class="col-3 flex-column">

                                                                <div
                                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; color:#0070C0">
                                                                    {{ strtoupper($co['coparcenar_name']) }}

                                                                </div>
                                                            </div>
                                                            <div class="col-3">
                                                                <div
                                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%;  color:#0070C0">
                                                                    {{ strtoupper($co['coparcener_type']) }}

                                                                </div>
                                                            </div>
                                                            <div class="col-3">
                                                                <div
                                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%;  color:#0070C0">
                                                                    {{ strtoupper($co['relation']) }}

                                                                </div>
                                                            </div>
                                                            <div class="col-3">
                                                                <div
                                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; color:#0070C0">
                                                                    {{ strtoupper($co['dob']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        <tr>
                                            <td height="8"></td>
                                        </tr>

                                        @if (isset($specialCase) && count($specialCase) > 0)
                                            <tr>
                                                <td height="8"></td>
                                            </tr>
                                            @foreach ($specialCase as $caseName => $caseValue)
                                                <tr>
                                                    <td style="line-height: 30px;" width="20%">
                                                        {{ $caseName }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="line-height: 30px;" width="30%">
                                                        <span
                                                            style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                            {{ $caseValue }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        <tr>
                                            <td height="8"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @else
                        @if (isset($specialCase) && count($specialCase) > 0)
                            <tr>
                                <td height="8"></td>
                            </tr>
                            @foreach ($specialCase as $caseName => $caseValue)
                                <tr>
                                    <td style="line-height: 30px;" width="20%">
                                        <label style="margin-left: 20px">{{ $caseName }}</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="line-height: 30px;">
                                        <span
                                            style="background:white;padding:0em 2.1em 0.2em 1.1em; width:20%;
                                        margin-left: 20px; height:100%; display: inline-block; color:#0070C0">
                                            {{ $caseValue }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <td style="padding-left: 10px!important;">
                                <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Existing Customer:
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    {{ strtoupper($customerOvdDetails['customer_full_name']) }}
                                                    [CUSTID: {{ $customerOvdDetails['customer_id'] }}]
                                                </span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="line-height: 30px;" width="20%">
                                                Mobile Number
                                            </td>
                                            <td style="line-height: 30px;" width="20%">
                                                Email
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    <label {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                        class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }} enc_label">
                                                        {{ $customerOvdDetails['mobile_number'] }}
                                                    </label>
                                                    @if ($is_review == 1 || $i_ao_trac)
                                                        <span class="maskingfield">*********</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td style="line-height: 30px;" width="30%">
                                                <span
                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                    <label {{ $is_review == 1 || $i_ao_trac ? 'style=display:none' : '' }}
                                                        class="{{ $is_review == 1 || $i_ao_trac ? 'unmaskingfield' : '' }} enc_label">
                                                        {{ $customerOvdDetails['email'] }}
                                                    </label>
                                                    @if ($is_review == 1 || $i_ao_trac)
                                                        <span class="maskingfield">*********</span>
                                                    @endif
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
                    @endif

                </tbody>
            </table>
        </span>
    </td>
</tr>
