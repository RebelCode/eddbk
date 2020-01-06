alter table `${cqrs/session_rules/table}` modify `repeat_monthly_on` enum('dotw', 'dotm');
alter table `${cqrs/session_rules/table}` modify `repeat_unit` enum('days', 'weeks', 'months', 'years');
alter table `${cqrs/session_rules/table}` modify `repeat_until` enum('date', 'period');
