drop table hms_cached_student_info;

CREATE TABLE hms_student_cache (
    banner_id           integer NOT NULL,
    term                integer NOT NULL,
    timestamp           integer NOT NULL,
    username            character varying(155) NOT NULL,
    last_name           character varying(255) NOT NULL,
    first_name          character varying(255) NOT NULL,
    middle_name         character varying(255),
    dob                 character(10) NOT NULL,
    gender              character(1) NOT NULL,
    deposit_date        character(10),
    type                character(1) NOT NULL,
    application_term    character(6) NOT NULL,
    class               character(2) NOT NULL,
    credit_hours        integer NOT NULL,
    student_level       character varying(16) NOT NULL,
    international       character varying(5) NOT NULL,
    honors              character varying(5) NOT NULL,
    teaching_fellow     character varying(5) NOT NULL,
    watauga_member      character varying(5) NOT NULL,
    PRIMARY KEY (banner_id, term)
);

CREATE INDEX hms_student_cache_usr_idx ON hms_student_cache(username);

CREATE TABLE hms_student_address_cache (
    banner_id       integer NOT NULL,
    atyp_code       character varying(2) NOT NULL,
    line1           character varying(255) NOT NULL,
    line2           character varying(255) NOT NULL,
    line3           character varying(255) NOT NULL,
    city            character varying(255) NOT NULL,
    state           character varying(255) NOT NULL,
    zip             character varying(10)  NOT NULL
);

CREATE INDEX hms_student_address_cache_idx ON hms_student_address_cache(banner_id);

CREATE TABLE hms_student_phone_cache (
    banner_id       integer NOT NULL,
    number          character varying(32) NOT NULL
);

CREATE INDEX hms_student_phone_cache_idx ON hms_student_phone_cache(banner_id);

CREATE TABLE hms_room_change_request (
    id                  INTEGER NOT NULL,
    state               INTEGER NOT NULL DEFAULT 0,
    term                INTEGER NOT NULL REFERENCES hms_term(term),
    curr_hall           INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    requested_bed_id    INTEGER REFERENCES hms_bed(id),
    reason              TEXT,
    cell_phone          VARCHAR(11),
    username            VARCHAR(32),
    denied_reason       TEXT,
    denied_by           VARCHAR(32),
    updated_on          INTEGER,
    switch_with         VARCHAR(32),
    is_swap             SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_participants (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    username            VARCHAR(32),
    name                VARCHAR(255),
    role                VARCHAR(255),
    added_on            INTEGER NOT NULL,
    updated_on          INTEGER NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE hms_room_change_preferences (
    id                  INTEGER NOT NULL,
    request             INTEGER NOT NULL REFERENCES hms_room_change_request(id),
    building            INTEGER NOT NULL REFERENCES hms_residence_hall(id),
    PRIMARY KEY(id)
);

INSERT INTO hms_permission (id, name, full_name) VALUES (2, 'room_change_approve', 'Approve Room Changes');
INSERT INTO hms_role_perm VALUES (1, 2);

ALTER TABLE hms_bed add column room_change_reserved SMALLINT NOT NULL DEFAULT(0)::smallint;
