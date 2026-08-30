<?php

namespace App\Models\Record7;

class Service extends Record7Model
{
    protected $table = 'record7_services';

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Settings where a second signature is not required by the setting itself.
     *
     * A supported-living tenancy and a person's own home ARE the person's own
     * home, and the rule written for a registered care home does not
     * automatically follow them there. Everything not on this list requires a
     * witness, including a children's home, whose position is unresolved and
     * flagged for the owner and a qualified reviewer.
     */
    private const SETTINGS_WITHOUT_A_WITNESS_RULE = [
        'supported_living',
        'persons_own_home',
    ];

    /**
     * Must a controlled drug movement here be witnessed?
     *
     * THIS FAILS SAFE, DELIBERATELY. A witness is required unless the house is
     * POSITIVELY identified as one of the settings above. A NULL setting, an
     * unrecognised value, or a house nobody has classified yet all require one.
     *
     * The reasoning, recorded so it is not quietly reversed: the harm of a
     * missing witness where one was needed outweighs the friction of an
     * unnecessary one, and defaulting the unknown case to lenient would drop
     * the requirement everywhere nobody had got round to filling in.
     *
     * A service may tighten this to 'always'. There is deliberately no value
     * that loosens it — a provider may add a control above the minimum, never
     * remove one.
     */
    public function controlledDrugWitnessRequired(): bool
    {
        if ($this->cd_witness_policy === 'always') {
            return true;
        }

        return ! in_array($this->care_setting, self::SETTINGS_WITHOUT_A_WITNESS_RULE, true);
    }

    /** Said plainly, for a screen. */
    public function witnessRuleExplained(): string
    {
        if ($this->cd_witness_policy === 'always') {
            return 'This service asks for a second signature on every controlled drug.';
        }

        return $this->controlledDrugWitnessRequired()
            ? 'A second signature is needed here.'
            : 'This is where the person lives, so a second signature is not required by the setting.';
    }
}
