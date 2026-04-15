<?php
namespace Company\Encrypt\Model;
use Company\Encrypt\CipherFunction;
use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;

class EncryptMapper extends \Company\SQL\Mapper
{

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE= 0;
    function tableName()
    {
        // TODO: Implement tableName() method.
        return "system_encrypt";
    }

    function tableAlias()
    {
        // TODO: Implement tableAlias() method.
        return "system_encrypt";
    }

    function updateEncrypt($encryptID, $input) {
        // if update
        if ($encryptID) {

            $this->makeInstance()->filterID($encryptID)->existsOrFail();
            $this->startTrans();
            $this->makeInstance()->filterID($encryptID)->update($input);
            $this->completeTransOrFail();

        } else {
            //validate required
            $required = ['id', 'algo'];
            foreach ($required as $field) {
                if (!strlen(trim($input[$field]))) {
                    throw new BadRequestException("Missing required field: " . $field);
                }
            }

            $this->makeInstance()->filterID($input["id"])->existsThenFail(new NotFoundException("File already exists"));

            $this->startTrans();
            $this->insert($input);
            $this->completeTransOrFail();
        }
    }

    function getEncrypt($encryptID){
        return $this->makeInstance()
            ->filterID($encryptID)
            ->getRow();
    }

    function getEncryptActive($active){
        return $this->makeInstance()
            ->filterActive($active)
            ->getRow();
    }

    function getAllEncrypt() {
        return $this->makeInstance()
            ->getAll()->toArray();

    }

    function deleteEncrypt($encryptID) {
        $this->makeInstance()->filterID($encryptID)->existsOrFail();
        $this->startTrans();
        $this->filterID($encryptID)->delete();
        $this->completeTransOrFail();
    }

    /**
     * @param string $encryptID
     * @param array $inputs
     * @return array
     */
    function encryptByID($encryptID, $inputs){
        $encryptInfo = $this->makeInstance()
            ->filterID($encryptID)
            ->getRow();

        foreach ($inputs as $key => $item){
            $inputs[$key] = CipherFunction::EnCipher($encryptInfo["algo"], $item, $encryptInfo["key_algo"]);
        }

        return $inputs;
    }

    /**
     * @param string $encryptID
     * @param array $inputs
     * @return array
     */
    function decryptByID($encryptID, $inputs){
        $encryptInfo = $this->makeInstance()
            ->filterID($encryptID)
            ->getRow();

        foreach ($inputs as $key => $item){
            $inputs[$key] = CipherFunction::DeCipher($encryptInfo["algo"], $item, $encryptInfo["key_algo"]);
        }

        return $inputs;
    }

    function filterActive($active) {
        $this->where("active=?", __FUNCTION__)->setParamWhere($active, __FUNCTION__);
        return $this;
    }
}