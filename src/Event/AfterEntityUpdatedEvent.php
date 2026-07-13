<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Event;

/**
 * This event is triggered after the updateEntity() call in the edit() flow
 * (and in the ajaxEdit() flow of toggleable boolean fields). It is NOT
 * triggered when creating entities in the new() flow: listen to the
 * AfterEntityPersistedEvent for that.
 *
 * @see AbstractCrudController::edit
 * @see AbstractCrudController::ajaxEdit
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * @template TEntity of object
 *
 * @extends AbstractLifecycleEvent<TEntity>
 */
final class AfterEntityUpdatedEvent extends AbstractLifecycleEvent
{
    use StoppableEventTrait;
}
