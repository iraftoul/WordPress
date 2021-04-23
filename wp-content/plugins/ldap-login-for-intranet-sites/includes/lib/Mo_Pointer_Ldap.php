<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 9/11/2018
 * Time: 9:48 AM
 */

class Mo_Pointer_Ldap
{

    private $content_ldap,$ldap_anchor_id,$ldap_edge,$ldap_align,$ldap_active,$ldap_pointer_name;

    function __construct($header,$body,$ldap_anchor_id,$ldap_edge,$ldap_align,$ldap_active,$prefix){

    $this->content_ldap = '<h3>' . __( $header ) . '</h3>';
    $this->content_ldap .= '<p  id="'.$prefix.'" style="font-size: initial;">' . __( $body ) . '</p>';
    $this-> ldap_anchor_id = $ldap_anchor_id;
    $this->ldap_edge = $ldap_edge;
    $this->ldap_align = $ldap_align;
    $this->ldap_active = $ldap_active;
    $this->ldap_pointer_name = 'miniorange_admin_pointer_'.$prefix;


    }


     function return_array(){
        return array(
            // The content needs to point to what we created above in the $new_pointer_content_ldap variable
            'content_ldap' => $this->content_ldap,

            // In order for the custom pointer to appear in the right location we need to specify the ID
            // of the element we want it to appear next to
            'ldap_anchor_id' => $this->ldap_anchor_id,

            // On what ldap_edge do we want the pointer to appear. Options are 'top', 'left', 'right', 'bottom'
            'ldap_edge' => $this->ldap_edge,

            // How do we want out custom pointer to ldap_align to the element it is attached to. Options are
            // 'left', 'right', 'center'
            'ldap_align' => $this->ldap_align,

            // This is how we tell the pointer to be dismissed or not. Make sure that the 'new_items'
            // string matches the string at the beginning of the array item
            'ldap_active' => $this->ldap_active
        );
    }

    /**
     * @return mixed
     */
    public function getcontent_ldap()
    {
        return $this->content_ldap;
    }

    /**
     * @param mixed $content_ldap
     */
    public function setcontent_ldap($content_ldap)
    {
        $this->content_ldap = $content_ldap;
    }

    /**
     * @return mixed
     */
    public function getAnchorId()
    {
        return $this->ldap_anchor_id;
    }


    /**
     * @return mixed
     */
    public function get_ldap_edge()
    {
        return $this->ldap_edge;
    }


    /**
     * @return mixed
     */
    public function get_ldap_active()
    {
        return $this->ldap_active;
    }

    /**
     * @param mixed $ldap_active
     */
    public function set_ldap_active($ldap_active)
    {
        $this->ldap_active = $ldap_active;
    }

    /**
     * @return mixed
     */
    public function getPointerName()
    {
        return $this->ldap_pointer_name;
    }

}