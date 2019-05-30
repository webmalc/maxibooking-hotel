<?php

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


trait TraitDocumentRelation
{
    /**
     * @return string|null
     */
    public function getSeries(): ?string
    {
        return $this->entity->getDocumentRelation() !== null
            ? $this->entity->getDocumentRelation()->getSeries()
            : null;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->entity->getDocumentRelation() !== null
            ? $this->entity->getDocumentRelation()->getNumber()
            : null;
    }

    /**
     * @return string|null
     */
    public function getIssued(): ?string
    {
        return ($this->entity->getDocumentRelation() !== null
            && $this->entity->getDocumentRelation()->getIssued() !== null)
            ? $this->entity->getDocumentRelation()->getIssued()->format('d.m.Y')
            : null;
    }

    /**
     * @return string|null
     */
    public function getExpiry(): ?string
    {
        return ($this->entity->getDocumentRelation() !== null
            && $this->entity->getDocumentRelation()->getExpiry() !== null)
            ? $this->entity->getDocumentRelation()->getExpiry()->format('d.m.Y')
            : null;
    }

    /**
     * @return string|null
     */
    public function getAuthorityOrganText(): ?string
    {
        $authorityOrganId = $this->getAuthorityOrganId();

        return $authorityOrganId !== null
            ? $this->container->get('mbh.billing.api')->getAuthorityOrganById($authorityOrganId)
            : null;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        $typeId = $this->getTypeId();

        return $typeId !== null
            ? $this->container->get('mbh.fms_dictionaries')->getDocumentTypes()[$typeId]
            : null;
    }

    /**
     * @return string|null
     */
    protected function getTypeId(): ?string
    {
        return $this->entity->getDocumentRelation() !== null
            ? $this->entity->getDocumentRelation()->getType()
            : null;
    }

    /**
     * @return string|null
     */
    protected function getAuthorityOrganId(): ?string
    {
        return $this->entity->getDocumentRelation() !== null
            ? $this->entity->getDocumentRelation()->getAuthorityOrganId()
            : null;
    }
}
