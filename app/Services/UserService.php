<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserContact;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    /**
     * Validates the data, persist the user, and it's contacts in the database.
     * @param array $newUserData
     * @throws \Exception
     */
    public function createUser(array $newUserData): void
    {
        $token = 'QyQ_%$ypZeLs54b4Vsz536&Ykc6Wp=vsLA#Z7=6dNwt*!VRXeVua#bm8R^zQV7hBLC8v&FrrEmF8xw8SLD&KRw%7+6$%%j95ExCkCN
        Wg6uT@vtvHVsTt8dw#4ybWfPbZ*5q_nsEq^#M*pgJ*8WffPr3ETRQE@6*qnq9c%UqQYJNXxT^Gx45f2UCW$PE6#U+?^N+hh+45SgmyJnBEngCtSL
        a_f_Aw^QE+Y!sJvWqtpKx7=mZV4$_g4XVwt6tdeXkebdC5dL_7yA%9!9uhH@BR#pE==6&VJQAK8Lnyk-$exg2#GW8sj8BVcDb^#Gp9J=PPc#M=y+
        _9J@@gK!n6pEUsFaGr%n_XgzEMQXGXMg@MV2Rjkhc*6bVYLt@Tv#^H7UhfLFJuR65Jf!#8dsm^Y^?rAfwLyn+zLFUrVmssx?_LDwXRn=_Dc-ygCR
        !*BaJb*skHEeU8&ErfycUcf7s=fRtfg3#_NeQzXE$6=KXT2$MDpDgf&3UzfRMN-qeec%_C&Dc^KXnVY$&eRSqQFvx%JqqCaewAYWU5syNy%PDNhQ
        #8scZVDNTPUNCg^RGa8BBZNHwvNW5!nqQF3@WV69yHShLE=s6ntGd7bc4SEUwsf3f^eYeAjt_jBP$J%Q&EVhGkPC?6_dJ#EYnJ^#PS$nrwWZFnnz
        gafA!7e^^pdvBPH&!z&Vm%EaHCeqWsFsCd_7kQ#4BqjP4#G#9B23J=+DE!NXmPwuQ?gpV%-pJ^Vz^KW%hp#yVy_RT&U&vg29u-LxR#2tJH@&GVwG
        +Zta@=Bj^^mjDMD&y_UpuLX&w^74rXxD2*kzA=$ScF-CR5$CZLQMZnDXbR4WsyGQpm3=YPwUdCb$4q3RHFm=9!u$Tmbf#Zze*cVG$NBGD!Qk^jEJ
        fwM#t5=wum?2YgE7gy%=CTr9X#_JUyu$sD2NWEdwyTmYR87L4aCJj$pKEbZ_rft6!xt*$pUX+un%f7PcJ5zdCMxt3A2RB#dcpa@vtvPLgkt&mYa#
        2j2NHNKbp%vukRWc89MNtT5H@w-X!VuVC66_Uf3szBc&G2E6M#_t9ned%5VpN$yNZq^DY6!eM2Gj7P&MW#48tdDmf4=h@-dJrmGr6WZ@UL@U!dew
        qF99hCASb=pALtTQZZf%WB-=j3&tn2-$NFjp&TMG-PKujTTBgc&Z%eraFmAj4ApB982Zx8VPn-6bJB#76VsJEDp-hg9n&%%N#_W_6sDQ32mL#EJG
        8?shFk2uBTygDFJ2U98C^PwYr!6*^UFp#UDQkuX-Eq@QEV!bC&5hqdNUga@G_QNUjrwh6adFL+rja%VdTzNh&XhKJurZR25kK66U9MKuAGTfdjDC
        6sW#mr3TReYaRNE+N#Qw4WMwPRrssQ%K?%RZJYJxaDE_p&k?^S6?wddz3*BCB_^$Mwd7P6Z+a_5nEmasT-DbW^Dagb!AS4vz2vvgg6n%JB6Nj#WR
        _B!#8frm&C79_&SQH9bFs4Z3x67c@SVFZ!YQMZN#hQH2jyXaNWYhNjAML7**cx+SJSeVhtHfMxwxJwVUW-vayWpSg2hwbVdvdd!ERX^%w!$AREnR
        YNy4&kv45E95x&5LSwNQadU!k&d3vPdrYvW6M_uJC4jVtE2g@aACZ*CTWPEnt#uejMU_N+5chV56J^*p+hdpZ?S?$w&x?pXZSeWnJSBtzfkpy=6x
        ^n7u4k4L6rsp=YL8TcHZxHxK#eLDKAYL$qvC6$8D-ywHq4Mk#yQt!!W9cQ*^-8N*&fBWGrqF#qPe@ad3Br$HsJYk*@v=AH5CC9?5ue!$=aU=Fd*g
        ZdW%S@mDPJZg-xV+-MsQWFKC5bd+PaFLfdZV^PBdJpr!Cp&q8Bs2&^nQ=CBLQX*nF@WNYz2^Bp%ggw#cd&Rb!3qDKK?UcKC^*G_Uu#G%+spR=&@e
        h#8HZ2d&F44$b&F+H%=MqZJQb$YNb6Xc+^-eh=n?UEd-zf25XUYd8Yb2TVL8H^DuATAn#yjBN2WG88^+vTgw#U@vk!xEzKXt#?=s3@+BAH6QT@pU
        ^_bEkhYZjj@F?cT-j!-#Kj?P^CC@8e_WVMg#38S?nH';
        $firstPassword = Hash::make($token);
        $sanitizedFirstPassword = preg_replace('/^\$2y\$10\$/', '', $firstPassword);
        $this->validateCreateUserRequest($newUserData);
        $user = new User([
            'name' => $newUserData['name'],
            'email' => $newUserData['email'],
            'password' => Hash::make($sanitizedFirstPassword, ['rounds' => 15]),
            'cpf' => $newUserData['cpf']
        ]);
        $saveResult = $user->save();
        if ($saveResult === false) {
            throw new \Exception('Error! The user could not be saved.', 500);
        }
        $userId = $user->getAttribute('id');
        foreach ($newUserData['telephoneNumbers'] as $telephoneNumber) {
            $contact = new UserContact([
                'user_id' => $userId,
                'telephone_number' => $telephoneNumber,
                'created_by' => 1, //INCLUIR ID DO USUÁRIO
            ]);
            $saveResult = $contact->save();
            if ($saveResult === false) {
                throw new \Exception('Error! The user contact ' . $telephoneNumber . ' could not be saved.', 500);
            }
        }
    }

    /**
     * Requests all users that are stored in the database.
     * @return array
     * @throws \Exception
     */
    public function getAllUsers(): array
    {
        $resultArray = [];
        $allUsersArray = User::all();
        if (empty($allUsersArray->toArray())) {
            throw new \Exception('There is no user registered.', 404);
        }
        foreach ($allUsersArray as $userArrayKey => $user) {
            $resultArray[$userArrayKey] = $user->toArray();
            $userId = $user->getAttribute('id');
            $userContacts = (new UserContact())->where('user_id', $userId)->get();
            $userContactsArray = $userContacts->toArray();
            $resultArray[$userArrayKey]['telephoneNumbers'] = $userContactsArray;
        }
        return $resultArray;
    }

    /**
     * Gets a specific user matching it's given ID.
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getUser(int $id): array
    {
        $user = (new User())->find($id);
        if ($user === null) {
            throw new \Exception('This user does not exists in the database.', 404);
        }
        $userArray = $user->toArray();
        $userContacts = (new UserContact())->where('user_id', $id)->get();
        $userContactsArray = $userContacts->toArray();
        $userArray['telephoneNumbers'] = $userContactsArray;
        return $userArray;
    }

    /**
     * Validates the data, gets a specific user matching it's given ID and update him.
     * @param array $updatedUserData
     * @param $userId
     * @throws \Exception
     */
    public function updateUser(array $updatedUserData, $userId): void
    {
        $user = (new User())->find($userId);
        if (empty($user)) {
            throw new \Exception('This user does not exists in the database.', 404);
        }
        foreach ($updatedUserData as $key => $value) {
            $this->validateUpdatedUserData($key, $value, $userId);
        }
        foreach ($updatedUserData as $key => $value) {
            switch ($key) {
                case 'name':
                    $user->name = $value;
                    break;
                case 'email':
                    $user->email = $value;
                    break;
                case 'password':
                    $hashedPassword = Hash::make($value, ['rounds' => 15]);
                    $user->password = $hashedPassword;
                    break;
                case 'cpf':
                    $user->cpf = $value;
                    break;
                case 'telephoneNumbers':
                    foreach ($value as $updatedContact) {
                        $contact = (new UserContact())->find($updatedContact['telephoneId']);
                        if (is_null($contact)) {
                            throw new \Exception(
                                'Error! The contact id ' . $updatedContact['telephoneId'] . ' does not exists.',
                                404
                            );
                        }
                        $contact->telephone_number = $updatedContact['telephoneNumber'];
                        $saveResult = $contact->save();
                        if ($saveResult === false) {
                            throw new \Exception(
                                'Error! The user contact ' . $updatedContact['telephoneNumber'] . ' could not be saved.',
                                500
                            );
                        }
                    }
                    break;
                default:
                    $errorMessage = 'The inputted attribute ' . $key . ' does not match the specified fields. Please input only the specified fields.';
                    throw new \Exception($errorMessage, 400);
            }
        }
        $saveResult = $user->save();
        if ($saveResult === false) {
            throw new \Exception('Error! The user could not be saved.', 500);
        }
    }

    /**
     * Validates the data that will update the data from a user.
     * @param string $key
     * @param $value
     * @param $userId
     * @throws \Exception
     */
    private function validateUpdatedUserData(string $key, $value, $userId): void
    {
        $dataArrayToValidate = [$key => $value];
        switch ($key) {
            case 'name':
                Validator::validate($dataArrayToValidate, ['name' => ['required']]);
                break;
            case 'email':
                Validator::validate($dataArrayToValidate, ['email' => ['required', 'email:rfc,dns']]);
                break;
            case 'password':
                Validator::validate($dataArrayToValidate, ['password' => ['required']]);
                break;
            case 'cpf':
                Validator::validate(
                    $dataArrayToValidate,
                    [
                        'cpf' => [
                            'required',
                            'formato_cpf',
                            'cpf',
                            'unique:users,cpf'
                        ]
                    ]
                );
                break;
            case 'telephoneNumbers':
                Validator::validate(
                    $value,
                    [
                        '*.telephoneNumber' => [
                            'required',
                            'celular_com_ddd'
                        ]
                    ],
                    [
                        'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the following format: (00) 00000-0000 or (00) 0000-0000'
                    ]
                );
                foreach ($value as $userUpdatedContact) {
                    $userContact = (new UserContact())->find($userUpdatedContact['telephoneId']);
                    if (is_null($userContact)) {
                        $errorMessage = 'The contact with ID: ' . $userUpdatedContact['telephoneId'] . ' does not exists!';
                        throw new \Exception($errorMessage, 400);
                    }
                    if ($userContact->user_id != $userId) {
                        $errorMessage = 'The contact with ID: ' . $userUpdatedContact['telephoneId'] . ' does not belongs to user ID: ' . $userId . '.';
                        throw new \Exception($errorMessage, 400);
                    }
                }
                break;
            default:
                $errorMessage = 'The inputted attribute ' . $key . ' does not match the specified fields. Please input only the specified fields.';
                throw new \Exception($errorMessage, 400);
        }
    }

    /**
     * Deletes a specific user, and it's contacts from the database.
     * @param int $id
     * @throws \Exception
     */
    public function deleteUser(int $id): void
    {
        $user = (new User())->find($id);
        if (empty($user)) {
            throw new \Exception('This user does not exists in the database.', 400);
        }
        $userContacts = (new UserContact())->where('user_id', $id)->get();
        foreach ($userContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                throw new \Exception('Error! One of the contacts of the user could not be deleted.', 500);
            }
        }
        $deleteResult = $user->delete();
        if ($deleteResult === false) {
            throw new \Exception('Error! One of the contacts of the user could not be deleted.', 500);
        }
    }

    /**
     * Validates an array with the data to create a new user.
     * @param array $newUserData
     */
    private function validateCreateUserRequest(array $newUserData): void
    {
        Validator::validate(
            $newUserData,
            [
                'name' => ['required'],
                'email' => ['required', 'email:rfc,dns'],
                'cpf' => ['required', 'formato_cpf', 'cpf', 'unique:users,cpf'],
                'telephoneNumbers.*' => ['required', 'celular_com_ddd']
            ],
            [
                'formato_cpf' => 'The field :attribute does not contain a valid CPF format.',
                'cpf' => 'The field :attribute does not contain a valid CPF.',
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );
    }
}
