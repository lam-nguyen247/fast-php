<?php
namespace Fast\Traits\Eloquent;

use Fast\Eloquent\Relationship\HasOneRelation;
use Fast\Eloquent\Relationship\BelongsToRelation;

trait RelationTraits {
	public function hasOne(string $model, string $remoteKey = '', string $localKey = ''): HasOneRelation {
		$relate = new HasOneRelation($model);
		$relate->setLocalKey($localKey);
		$relate->setRemoteKey($remoteKey);
		return $relate;
	}

	public function belongsTo(string $model, string $localKey = '', string $remoteKey = ''): BelongsToRelation {
		$relate = new BelongsToRelation($model);
		$relate->setLocalKey($localKey);
		$relate->setRemoteKey($remoteKey);
		return $relate;
	}
}