import React from 'react';
import StatusLabel from './StatusLabel.jsx';
import Icon from './Icon.jsx';

/**
 * One person, and only what decides whether you go to them next.
 *
 * Who, when they are due, whether they are late, how many things they are
 * waiting for, and anything that could hurt them. That is the whole card.
 *
 * NOT what those medicines are. Naming them turned five people into three
 * screenfuls and answered a question nobody is asking yet — you are deciding
 * who to walk to, not what to hand over. The names live on the round screen,
 * where somebody is holding the box.
 */
export default function PersonDueCard({ person }) {
    return (
        <li className={`r7-due${person.isLate ? ' r7-due--late' : ''}`}>
            <div className="r7-due__head">
                <div className="r7-due__who">
                    <span className="r7-due__name">{person.name}</span>
                    <span className="r7-due__meta">
                        {person.room ? <span>{person.room}</span> : null}
                        <span>
                            {person.medicineCount} {person.medicineCount === 1 ? 'medicine' : 'medicines'} due
                        </span>
                        {person.timeCritical ? (
                            <span className="r7-due__critical">Time critical</span>
                        ) : null}
                        {person.changed ? (
                            <span className="r7-due__changed">Recently changed</span>
                        ) : null}
                    </span>
                </div>

                <div className="r7-due__when">
                    {person.isLate ? (
                        <StatusLabel tone="error">Late</StatusLabel>
                    ) : (
                        <span className="r7-due__time">{person.nextDueAt}</span>
                    )}
                    <span className="r7-due__slot">{person.slot}</span>
                </div>
            </div>

            {person.criticalAllergies.length ? (
                <p className="r7-allergy" role="note">
                    <Icon name="warning" className="r7-icon r7-icon--small" />
                    <span>
                        <strong>Allergy</strong>
                        {person.criticalAllergies.map((allergy) => (
                            <span key={allergy.substance} className="r7-allergy__item">
                                {allergy.substance} — {allergy.severity}
                            </span>
                        ))}
                    </span>
                </p>
            ) : null}
        </li>
    );
}
